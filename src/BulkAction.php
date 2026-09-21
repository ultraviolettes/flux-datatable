<?php

namespace Ultraviolettes\FluxDataTable;

use Illuminate\Support\Str;

class BulkAction
{
    public bool $requiresConfirmation = false;

    public string $slug;

    public ?\Closure $callback = null;

    public string $confirmationIcon = 'exclamation-triangle';

    public ?string $icon = null;

    /**
     * Variante Flux du bouton : `outline` par défaut, `danger` pour une action
     * destructrice, qui doit se distinguer d'un simple rangement.
     */
    public string $variant = 'outline';

    /**
     * Texte de la modale de confirmation. `null` : texte générique traduit.
     */
    public ?string $confirmationText = null;

    /**
     * Rend la raison pour laquelle l'action est indisponible sur une sélection,
     * ou `null` si elle est permise.
     */
    public ?\Closure $disabledWhen = null;

    public function __construct(public string $label)
    {
        $this->slug = Str::slug($label);
    }

    public static function make(string $label): self
    {
        return new self($label);
    }

    public function action(\Closure $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function apply($selected): void
    {
        call_user_func($this->callback, $selected);
    }

    public function requiresConfirmation(): self
    {
        $this->requiresConfirmation = true;

        return $this;

    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function variant(string $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function confirmationText(string $confirmationText): self
    {
        $this->confirmationText = $confirmationText;

        return $this;
    }

    /**
     * Rend l'action indisponible selon la sélection.
     *
     * Le callback reçoit les ids sélectionnés et rend la raison de
     * l'indisponibilité, affichée en infobulle sur le bouton grisé, ou `null`
     * si l'action est permise. On exige une raison plutôt qu'un booléen : un
     * bouton grisé sans explication laisse l'utilisateur sans recours.
     *
     * @param  \Closure(array): ?string  $callback
     */
    public function disabledWhen(\Closure $callback): self
    {
        $this->disabledWhen = $callback;

        return $this;
    }

    /**
     * La raison pour laquelle l'action est indisponible sur cette sélection,
     * ou `null` si elle est permise.
     *
     * Une sélection vide n'a pas de raison : tous les boutons sont grisés et le
     * tableau sans coche se suffit à lui-même.
     */
    public function disabledReasonFor(array $selected): ?string
    {
        if ($selected === [] || $this->disabledWhen === null) {
            return null;
        }

        return call_user_func($this->disabledWhen, $selected);
    }

    public function isAvailableFor(array $selected): bool
    {
        return $selected !== [] && $this->disabledReasonFor($selected) === null;
    }

    public function confirmationIcon(string $confirmationIcon): self
    {
        $this->confirmationIcon = $confirmationIcon;

        return $this;
    }
}
