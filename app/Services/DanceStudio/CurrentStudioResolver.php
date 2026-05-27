<?php

namespace App\Services\DanceStudio;

use App\Models\DanceStudio\Studio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurrentStudioResolver
{
    private bool $resolved = false;

    private ?Studio $resolvedStudio = null;

    public function __construct(
        private readonly Request $request
    ) {}

    public function id(): int
    {
        return (int) ($this->studio()?->id ?? 1);
    }

    public function studio(): ?Studio
    {
        if ($this->resolved) {
            return $this->resolvedStudio;
        }

        $this->resolved = true;

        $userStudioId = 0;
        if (Auth::check()) {
            $userStudioId = (int) (Auth::user()?->studio_id ?? 0);
        }

        if ($userStudioId > 0) {
            $studio = Studio::query()->whereKey($userStudioId)->first();
            if ($studio !== null) {
                return $this->resolvedStudio = $studio;
            }
        }

        $queryStudioId = $this->request->query('studio_id');
        $queryStudioId = is_numeric($queryStudioId) ? (int) $queryStudioId : 0;
        if ($queryStudioId > 0) {
            $studio = Studio::query()->whereKey($queryStudioId)->first();
            if ($studio !== null) {
                return $this->resolvedStudio = $studio;
            }
        }

        $demoStudio = Studio::query()
            ->where('name', 'Demo Dance Studio')
            ->orderBy('id')
            ->first();
        if ($demoStudio !== null) {
            return $this->resolvedStudio = $demoStudio;
        }

        $firstStudio = Studio::query()->orderBy('id')->first();
        if ($firstStudio !== null) {
            return $this->resolvedStudio = $firstStudio;
        }

        return $this->resolvedStudio = null;
    }
}

