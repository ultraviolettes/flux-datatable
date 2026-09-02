<?php

namespace Ultraviolettes\FluxDataTable\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

/**
 * Filtre à valeur multiple : l'utilisateur choisit plusieurs valeurs sur une
 * même dimension, et le tableau affiche leur union (`whereIn` par défaut).
 *
 * Associé à `defaultValue()`, c'est la façon de masquer une valeur par défaut
 * (« tout sauf les commandes annulées ») sans introduire un second filtre
 * d'exclusion qui pourrait contredire le premier.
 */
final class MultiSelectFilter extends Filter
{
    protected array $options = [];

    /**
     * Set the options for the multi-select filter
     *
     * @return $this
     */
    public function options(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get the options for the multi-select filter
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Apply the filter to the query
     *
     * @param  Builder  $query
     * @param  mixed  $value
     */
    public function apply($query, $value): Builder
    {
        // `empty([])` est vrai : une sélection entièrement vidée n'applique
        // aucun filtre, le tableau montre alors toutes les lignes. C'est
        // délibéré (cf. test) — ne pas « réparer » ce court-circuit.
        if (empty($value)) {
            return $query;
        }

        $values = array_values((array) $value);

        // Le callback applicatif reçoit toujours un tableau, même si l'utilisateur
        // n'a sélectionné qu'une seule valeur.
        if ($this->queryCallback) {
            return call_user_func($this->queryCallback, $query, $values);
        }

        return $query->whereIn($this->field, $values);
    }

    public function getKeyLabel(string $key): string
    {
        return $this->options[$key] ?? $key;
    }

    /**
     * Libellé de la pill.
     *
     * Une sélection quasi complète ne doit pas s'afficher comme une énumération :
     * on nomme alors les valeurs absentes, et on retombe sur un simple décompte
     * quand même cette liste serait trop longue.
     */
    public function getPillLabel(mixed $value): string
    {
        $selected = array_map('strval', array_values((array) $value));
        $missing = array_diff(array_map('strval', array_keys($this->options)), $selected);

        if ($this->options !== [] && $missing === []) {
            return __('flux-datatable::flux-datatable.filter_all');
        }

        if (count($selected) <= 2) {
            return $this->joinLabels($selected);
        }

        if (count($missing) <= 2) {
            return __('flux-datatable::flux-datatable.filter_all_except', [
                'values' => $this->joinLabels($missing),
            ]);
        }

        return __('flux-datatable::flux-datatable.filter_selected_count', [
            'count' => count($selected),
        ]);
    }

    /**
     * Render the filter
     */
    public function render(): View
    {
        return view('flux-datatable::filters.multi-select', [ // @phpstan-ignore-line
            'name' => $this->name,
            'field' => $this->field,
            'options' => $this->options,
        ]);
    }

    /**
     * Create a new MultiSelectFilter instance
     *
     * @return static
     */
    public static function make(string $name, string $field): self
    {
        return new self($name, $field);
    }

    /**
     * @param  array<int, string>  $keys
     */
    private function joinLabels(array $keys): string
    {
        return implode(', ', array_map(fn (string $key) => $this->getKeyLabel($key), $keys));
    }
}
