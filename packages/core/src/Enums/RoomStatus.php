<?php

namespace Amu\Core\Enums;

enum RoomStatus: string
{
    case Waiting = 'waiting';
    case Active = 'active';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}
