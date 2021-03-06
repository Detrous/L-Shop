<?php
declare(strict_types = 1);

namespace App\Repository\User;

use App\Entity\User;
use Doctrine\ORM\Internal\Hydration\IterableResult;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepository
{
    public function create(User $user): void;

    public function update(User $user): void;

    public function remove(User $user): void;

    public function deleteAll(): bool;

    public function find(int $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;

    public function findPaginated(int $page, int $perPage): LengthAwarePaginator;

    public function findPaginatedWithOrder(string $orderBy, bool $descending, int $page, int $perPage): LengthAwarePaginator;

    public function findPaginateWithSearch(string $search, int $page, int $perPage): LengthAwarePaginator;

    public function findPaginatedWithOrderAndSearch(string $orderBy, bool $descending, string $search, int $page, int $perPage): LengthAwarePaginator;

    public function findAllAsIterable(): IterableResult;

    public function retrieveCreatedForYear(): array;

    public function retrieveCreatedForMonth(int $year, int $month): array;

    public function retrieveCreatedAmount(): int;
}
