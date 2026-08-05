<?php

namespace App\Contracts;

use App\DTOs\GateUserDTO;

interface GateClientContract
{
    /**
     * Fetch paginated users payload from Gate SSO API.
     *
     * @return array{data: GateUserDTO[], page: int, total_pages: int, total_items: int}
     */
    public function fetchUsers(int $page = 1, int $perPage = 50): array;

    /**
     * Fetch a single user by Gate User ID.
     */
    public function fetchUserById(string $gateUserId): ?GateUserDTO;

    /**
     * Ping Gate service endpoint health.
     */
    public function ping(): bool;
}
