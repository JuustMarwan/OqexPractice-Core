<?php

namespace xSuper\OqexPractice\bot;

enum BotType{

	case DummyNoDebuff;
	case EasyNoDebuff;
	case NormalNoDebuff;
	case HardNoDebuff;
	case GodlyNoDebuff;

    case DummyArcher;
    case EasyArcher;
    case NormalArcher;
    case HardArcher;
    case GodlyArcher;

    case DummySumo;
    case EasySumo;
    case NormalSumo;
    case HardSumo;
    case GodlySumo;

    case DummyGapple;
    case EasyGapple;
    case NormalGapple;
    case HardGapple;
    case GodlyGapple;

    case DummySoup;
    case EasySoup;
    case NormalSoup;
    case HardSoup;
    case GodlySoup;
}