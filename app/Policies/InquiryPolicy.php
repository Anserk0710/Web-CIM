<?php

// path: app/Policies/InquiryPolicy.php

namespace App\Policies;

use App\Models\Inquiry;
use App\Models\User;

class InquiryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission(
            'inquiry.read',
        );
    }

    public function view(
        User $user,
        Inquiry $inquiry,
    ): bool {
        return $user->hasPermission(
            'inquiry.read',
        );
    }

    public function update(
        User $user,
        Inquiry $inquiry,
    ): bool {
        return $user->hasPermission(
            'inquiry.update',
        );
    }

    public function export(User $user): bool
    {
        return $user->hasPermission(
            'inquiry.read',
        )
            && $user->hasPermission(
                'inquiry.export',
            );
    }

    public function delete(
        User $user,
        Inquiry $inquiry,
    ): bool {
        return $user->hasPermission(
            'inquiry.delete',
        );
    }
}
