<?php

namespace Amu\Core\Enums;

enum ParticipantConnection: string
{
    case Connected = 'connected';
    case Disconnected = 'disconnected';
    case Reconnected = 'reconnected';
}
