<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    /** Admins manage every article; a guru only manages their own. */
    public function update(User $user, News $news): bool
    {
        return $user->isAdmin() || $news->user_id === $user->id;
    }

    public function delete(User $user, News $news): bool
    {
        return $this->update($user, $news);
    }
}
