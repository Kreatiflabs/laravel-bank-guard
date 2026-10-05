<?php

namespace Kreatiflabs\BankGuard\Data;

use Illuminate\Support\Collection;
use Kreatiflabs\BankGuard\Enums\BankCategory;
use Kreatiflabs\BankGuard\Exceptions\BankNotFoundException;
use Kreatiflabs\BankGuard\Models\Bank;

class BankRepository
{
    /**
     * @var Collection<string, Bank>|null
     */
    protected ?Collection $banks = null;

    public function __construct(
        protected ?string $dataPath = null,
        protected array $customBanks = []
    ) {
        $this->dataPath = $dataPath ?: __DIR__ . '/../../resources/data/banks.json';
    }

    /**
     * Get all banks as a Collection.
     *
     * @return Collection<int, Bank>
     */
    public function all(): Collection
    {
        $this->ensureLoaded();

        return $this->banks->values();
    }

    /**
     * Find a bank by code, alias, short name, or swift code.
     */
    public function find(string $identifier): ?Bank
    {
        $this->ensureLoaded();

        $trimmed = trim($identifier);
        if ($trimmed === '') {
            return null;
        }

        return $this->banks->first(fn (Bank $bank) => $bank->matches($trimmed));
    }

    /**
     * Find a bank or throw an exception if not found.
     *
     * @throws BankNotFoundException
     */
    public function findOrFail(string $identifier): Bank
    {
        $bank = $this->find($identifier);

        if (!$bank) {
            throw BankNotFoundException::forIdentifier($identifier);
        }

        return $bank;
    }

    /**
     * Determine if a bank identifier exists.
     */
    public function exists(string $identifier): bool
    {
        return $this->find($identifier) !== null;
    }

    /**
     * Get banks filtered by category.
     *
     * @return Collection<int, Bank>
     */
    public function getByCategory(BankCategory|string $category): Collection
    {
        $categoryEnum = is_string($category)
            ? BankCategory::tryFrom(strtolower($category))
            : $category;

        if (!$categoryEnum) {
            return collect();
        }

        return $this->all()->filter(fn (Bank $bank) => $bank->category === $categoryEnum)->values();
    }

    /**
     * Search banks by keyword in code, name, or aliases.
     *
     * @return Collection<int, Bank>
     */
    public function search(string $query): Collection
    {
        $keyword = strtolower(trim($query));
        if ($keyword === '') {
            return $this->all();
        }

        return $this->all()->filter(function (Bank $bank) use ($keyword) {
            if (str_contains(strtolower($bank->code), $keyword)) {
                return true;
            }
            if (str_contains(strtolower($bank->short_name), $keyword)) {
                return true;
            }
            if (str_contains(strtolower($bank->name), $keyword)) {
                return true;
            }
            foreach ($bank->aliases as $alias) {
                if (str_contains(strtolower($alias), $keyword)) {
                    return true;
                }
            }
            return false;
        })->values();
    }

    /**
     * Get list of all bank codes.
     *
     * @return array<int, string>
     */
    public function codes(): array
    {
        return $this->all()->pluck('code')->all();
    }

    /**
     * Ensure bank data is loaded from file and merged with custom configurations.
     */
    protected function ensureLoaded(): void
    {
        if ($this->banks !== null) {
            return;
        }

        $items = [];

        if (file_exists($this->dataPath)) {
            $json = file_get_contents($this->dataPath);
            $decoded = json_decode($json, true) ?: [];
            foreach ($decoded as $item) {
                $bank = Bank::fromArray($item);
                $items[$bank->code] = $bank;
            }
        }

        // Merge custom or override banks
        foreach ($this->customBanks as $item) {
            $bank = Bank::fromArray($item);
            $items[$bank->code] = $bank;
        }

        $this->banks = collect($items);
    }

    /**
     * Refresh internal bank cache.
     */
    public function refresh(): void
    {
        $this->banks = null;
        $this->ensureLoaded();
    }
}
