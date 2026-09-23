<?php

namespace Ultraviolettes\FluxDataTable;

use Illuminate\Support\Str;

class BulkAction
{
    public bool $requiresConfirmation = false;

    /**
     * Texte du bouton : une chaîne, ou une closure qui reçoit la sélection
     * (« Ajouter au devis (7) »). Par défaut, dérivé du nom (`move-to-folder`
     * → « Move To Folder »). À lire avec `labelFor()`.
     *
     * @var string|\Closure(array): string
     */
    public string|\Closure $label;

    public ?\Closure $callback = null;

    public string $confirmationIcon = 'exclamation-triangle';

    public ?string $icon = null;

    /**
     * Variante Flux du bouton : `outline` par défaut, `primary` pour l'action
     * principale, `danger` pour une action destructrice. Dans le bandeau,
     * `danger` est rendu en texte rouge sans fond (voir `buttonVariant()`).
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

    /**
     * Rend la note de portée de l'action sur une sélection, ou `null`.
     */
    public ?\Closure $scopeNote = null;

    /**
     * @param  string  $name  Identifiant stable de l'action, écrit par le développeur :
     *                        c'est lui qu'appelle `executeBulkAction()`. Il ne dérive pas
     *                        du libellé, pour qu'un renommage ou une traduction du bouton
     *                        ne change pas l'action appelée.
     */
    public function __construct(public string $name)
    {
        // Le nom finit dans un `wire:click="executeBulkAction('…')"` et un nom de
        // modale : on le restreint à ce qui y passe sans échappement.
        if (! preg_match('/^[A-Za-z0-9_-]+$/', $name)) {
            throw new \InvalidArgumentException(
                "Bulk action name [{$name}] must only contain letters, digits, dashes and underscores."
            );
        }

        $this->label = Str::headline($name);
    }

    public static function make(string $name): self
    {
        return new self($name);
    }

    /**
     * @param  string|\Closure(array): string  $label  Une closure reçoit les ids
     *                                                 sélectionnés, pour un libellé
     *                                                 qui porte un compte que seul
     *                                                 le consommateur connaît.
     */
    public function label(string|\Closure $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Le texte du bouton pour cette sélection.
     */
    public function labelFor(array $selected): string
    {
        return $this->label instanceof \Closure
            ? (string) call_user_func($this->label, $selected)
            : $this->label;
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

    /**
     * La variante Flux effectivement rendue.
     *
     * `danger` devient un bouton fantôme rouge : un bouton plein rouge attire
     * trop l'œil, surtout sur le bandeau foncé, pour une action qu'on déclenche
     * rarement. La couleur passe par `buttonColor()`.
     */
    public function buttonVariant(): string
    {
        return $this->variant === 'danger' ? 'ghost' : $this->variant;
    }

    public function buttonColor(): ?string
    {
        return $this->variant === 'danger' ? 'red' : null;
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

    /**
     * Précise la portée de l'action sans la griser.
     *
     * Le callback reçoit les ids sélectionnés et rend une note affichée en
     * seconde ligne du bandeau (« Déplacer ne s'applique qu'aux 3 fichiers »),
     * ou `null`. À réserver à une action qui a un sens sur une partie de la
     * sélection ; quand elle n'en a aucun, `disabledWhen()` la grise.
     *
     * @param  \Closure(array): ?string  $callback
     */
    public function scopeNote(\Closure $callback): self
    {
        $this->scopeNote = $callback;

        return $this;
    }

    /**
     * La note de portée de l'action sur cette sélection, ou `null`.
     *
     * Une action indisponible n'a pas de note : grisée, elle ne s'applique à
     * rien, et sa raison est déjà dans l'infobulle.
     */
    public function scopeNoteFor(array $selected): ?string
    {
        if ($this->scopeNote === null || ! $this->isAvailableFor($selected)) {
            return null;
        }

        return call_user_func($this->scopeNote, $selected);
    }

    public function isAvailableFor(array $selected): bool
    {
        return $selected !== [] && $this->disabledReasonFor($selected) === null;
    }

    /**
     * Nom de la modale de confirmation de l'action dans ce composant.
     *
     * Les noms de modale sont globaux dans la page : on les préfixe par l'id du
     * composant pour que deux tables ayant chacune une action `delete` ne
     * s'ouvrent pas la modale l'une de l'autre.
     */
    public function modalName(string $componentId): string
    {
        return 'confirm-modal-'.$componentId.'-'.$this->name;
    }

    public function confirmationIcon(string $confirmationIcon): self
    {
        $this->confirmationIcon = $confirmationIcon;

        return $this;
    }
}
