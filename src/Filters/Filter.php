<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

abstract class Filter
{
    protected string $name;

    protected string $field;

    protected ?\Closure $queryCallback = null; // Callback pour la query personnalisée

    protected array $options = [];

    public string|array|null $defaultValue = null;

    public bool $showPills = true;

    public function __construct(string $name, string $field)
    {
        $this->name = $name;
        $this->field = $field;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Set a custom query callback for the filter.
     *
     * @return $this
     */
    public function query(callable $callback): self
    {
        $this->queryCallback = $callback;

        return $this;
    }

    /**
     * Apply the filter to the query
     *
     * @param  Builder  $query
     * @param  mixed  $value
     */
    public function apply($query, $value): Builder
    {
        if (empty($value)) {
            return $query;
        }

        // Si une callback est définie via `query()`, appelez-la
        if ($this->queryCallback) {
            return call_user_func($this->queryCallback, $query, $value);
        }

        return $query->where($this->field, $value);
    }

    public function getKeyLabel(string $key): string
    {
        return Str::ucfirst($key);
    }

    /**
     * Libellé affiché dans la pill du filtre actif.
     *
     * Point d'extension distinct de `getKeyLabel()`, qui ne reçoit qu'une clé
     * scalaire : un filtre à valeur multiple le surcharge sans obliger tous les
     * autres filtres à élargir leur signature.
     */
    public function getPillLabel(mixed $value): string
    {
        return $this->getKeyLabel($value);
    }

    /**
     * Valeur pré-sélectionnée, posée sur le composant au `mount()`.
     *
     * Accepte un tableau pour les filtres à valeur multiple (MultiSelectFilter).
     */
    public function defaultValue(string|array $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function showPills(bool $show): self
    {
        $this->showPills = $show;

        return $this;
    }

    abstract public function render();

    /**
     * Create a new SelectFilter instance
     *
     * @return static
     */
    abstract public static function make(string $name, string $field): self;
}
