<?php

namespace Amu\Core\Enums;

enum ParticipantRole: string
{
    case Joined = 'joined';
    case Seated = 'seated';
    case Spectator = 'spectator';
}
