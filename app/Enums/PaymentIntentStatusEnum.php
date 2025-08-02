<?php

namespace App\Enums;

enum PaymentIntentStatusEnum: string
{
    case Open = 'open';
    case Canceled = 'canceled';
    case Processing = 'processing';
    case RequiresAction = 'requires_action';
    case RequiresCapture = 'requires_capture';
    case RequiresConfirmation = 'requires_confirmation';
    case RequiresPaymentMethod = 'requires_payment_method';
    case Succeeded = 'succeeded';

    public function label(): string
    {
        return match ($this) {
            self::Open => __('payment_intents.open'),
            self::Canceled => __('payment_intents.canceled'),
            self::Processing => __('payment_intents.processing'),
            self::RequiresAction => __('payment_intents.requires_action'),
            self::RequiresCapture => __('payment_intents.requires_capture'),
            self::RequiresConfirmation => __('payment_intents.requires_confirmation'),
            self::RequiresPaymentMethod => __('payment_intents.requires_payment_method'),
            self::Succeeded => __('payment_intents.succeeded'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'gray',
            self::Canceled => 'danger',
            self::Processing => 'warning',
            self::RequiresAction,
            self::RequiresCapture,
            self::RequiresConfirmation,
            self::RequiresPaymentMethod => 'gray',
            self::Succeeded => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Open => 'heroicon-o-clock',
            self::Canceled => 'heroicon-o-x-circle',
            self::Processing => 'heroicon-o-clock',
            self::RequiresAction => 'heroicon-o-exclamation-circle',
            self::RequiresCapture => 'heroicon-o-arrow-down-tray',
            self::RequiresConfirmation => 'heroicon-o-check-circle',
            self::RequiresPaymentMethod => 'heroicon-o-credit-card',
            self::Succeeded => 'heroicon-o-check-badge',
        };
    }
}
