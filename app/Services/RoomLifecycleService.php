<?php

namespace App\Services;

use App\Enums\RoomMemberRole;
use App\Enums\RoomStatus;
use App\Events\Room\HostTransferred;
use App\Events\Room\RoomCancelled;
use App\Events\Room\RoomClosed;
use App\Events\Room\RoomStatusChanged;
use App\Models\GameRoom;
use App\Models\GameRoomMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RoomLifecycleService
{
    public function __construct(private readonly AuditService $audit) {}

    // -------------------------------------------------------------------------
    // State transitions
    // -------------------------------------------------------------------------

    /**
     * Transition the room to a new status.
     *
     * @throws RuntimeException if the transition is invalid.
     */
    public function transitionTo(GameRoom $room, RoomStatus $next): GameRoom
    {
        if (! $room->status->canTransitionTo($next)) {
            throw new RuntimeException(
                "Cannot transition room #{$room->id} from [{$room->status->value}] to [{$next->value}]."
            );
        }

        $room->status = $next;
        $room->save();

        broadcast(new RoomStatusChanged($room))->toOthers();

        return $room;
    }

    /**
     * Recalculate whether the room status should be 'ready' or back to 'waiting'
     * based on whether all active players have toggled ready.
     * Only called while the room is in waiting/ready states.
     */
    public function recalculateReadyStatus(GameRoom $room): GameRoom
    {
        if (! in_array($room->status, [RoomStatus::Waiting, RoomStatus::Ready], strict: true)) {
            return $room;
        }

        $newStatus = $room->allPlayersReady() ? RoomStatus::Ready : RoomStatus::Waiting;

        if ($room->status !== $newStatus) {
            $room->status = $newStatus;
            $room->save();
            broadcast(new RoomStatusChanged($room))->toOthers();
        }

        return $room;
    }

    // -------------------------------------------------------------------------
    // Close / cancel
    // -------------------------------------------------------------------------

    /**
     * Close a room (graceful operator-initiated closure after game ends).
     *
     * @param  string  $reason  Optional reason for audit log.
     */
    public function closeRoom(GameRoom $room, ?User $actor = null, string $reason = 'manual'): GameRoom
    {
        return DB::transaction(function () use ($room, $actor, $reason) {
            $this->transitionTo($room, RoomStatus::Closed);
            $this->removeMembersFromRoom($room);

            $this->audit->roomClosed($room, $reason);
            broadcast(new RoomClosed($room))->toOthers();

            return $room;
        });
    }

    /**
     * Cancel a room (e.g. host left before game started, or operator action).
     */
    public function cancelRoom(GameRoom $room, ?User $actor = null, string $reason = 'manual'): GameRoom
    {
        return DB::transaction(function () use ($room, $actor, $reason) {
            $this->transitionTo($room, RoomStatus::Cancelled);
            $this->removeMembersFromRoom($room);

            $this->audit->roomCancelled($room, $reason);
            broadcast(new RoomCancelled($room))->toOthers();

            return $room;
        });
    }

    // -------------------------------------------------------------------------
    // Host transfer
    // -------------------------------------------------------------------------

    /**
     * Transfer host ownership to another active member.
     *
     * @throws RuntimeException if newHost is not an active member.
     */
    public function transferHost(GameRoom $room, User $newHost): GameRoom
    {
        return DB::transaction(function () use ($room, $newHost) {
            $newMember = $room->activeMembers()->where('user_id', $newHost->id)->first();

            if (! $newMember) {
                throw new RuntimeException("User #{$newHost->id} is not an active member of room #{$room->id}.");
            }

            // Demote current host to player
            $room->activeMembers()
                ->where('role', RoomMemberRole::Host->value)
                ->update(['role' => RoomMemberRole::Player->value, 'is_ready' => false]);

            // Promote new host
            $newMember->update([
                'role'     => RoomMemberRole::Host->value,
                'is_ready' => true,
            ]);

            $room->host_user_id = $newHost->id;
            $room->save();

            broadcast(new HostTransferred($room, $newHost))->toOthers();

            return $room->fresh();
        });
    }

    // -------------------------------------------------------------------------
    // Handle host leaving
    // -------------------------------------------------------------------------

    /**
     * Called when the host leaves the room. Transfers host to the next player,
     * or cancels the room if no other players remain.
     */
    public function handleHostLeft(GameRoom $room, User $leavingHost): GameRoom
    {
        $nextHost = $room->activeMembers()
            ->where('user_id', '!=', $leavingHost->id)
            ->whereNull('left_at')
            ->whereNot('role', RoomMemberRole::Spectator->value)
            ->orderBy('joined_at')
            ->first()?->user;

        if ($nextHost) {
            return $this->transferHost($room, $nextHost);
        }

        // No remaining players — cancel the room
        return $this->cancelRoom($room, $leavingHost, 'host_left_empty');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function removeMembersFromRoom(GameRoom $room): void
    {
        $room->activeMembers()->update(['left_at' => now()]);
    }
}
