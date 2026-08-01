<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Application;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Application');
    }

    public function view(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('View:Application');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Application');
    }

    public function update(AuthUser $authUser, Application $application): bool
    {
        if (!empty($application->PAYMENT_FLAG) && $application->PAYMENT_FLAG !== \App\Enums\PaymentMethodEnum::NONE) {
            return false;
        }

        if ((bool)$application->ACCEPTED || (bool)$application->CONFIRMED_BY_APPLICANT) {
            return false;
        }

        if ($application->STATUS !== null && $application->STATUS !== \App\Enums\ApplicationStatus::New) {
            return false;
        }

        return $authUser->can('Update:Application');
    }

    public function delete(AuthUser $authUser, Application $application): bool
    {
        if (!empty($application->PAYMENT_FLAG) && $application->PAYMENT_FLAG !== \App\Enums\PaymentMethodEnum::NONE) {
            return false;
        }

        return $authUser->can('Delete:Application');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function accept(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Accept:Application');
    }

    public function cancelAccept(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('CancelAccept:Application');
    }

    public function pay(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Pay:Application');
    }

    public function confirm(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('Confirm:Application');
    }

    public function cancelConfirm(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('CancelConfirm:Application');
    }

    public function cancelPayment(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('CancelPayment:Application');
    }

    public function printReceipt(AuthUser $authUser, Application $application): bool
    {
        return $authUser->can('PrintReceipt:Application');
    }
}