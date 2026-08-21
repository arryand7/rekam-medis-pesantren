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
     * Download photo from a temporary signed URL provided by Gate SSO.
     * Returns binary content of the image, or null on failure.
     */
    public function downloadPhoto(string $signedUrl): ?string;

    /**
     * Ping Gate service endpoint health.
     */
    public function ping(): bool;
}
