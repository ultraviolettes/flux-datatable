<?php

namespace Ultraviolettes\FluxDataTable\Tests;

use Flux\FluxServiceProvider;
use FluxPro\FluxProServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;
use Livewire\Blaze\BlazeServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Ultraviolettes\FluxDataTable\FluxDataTableServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'Ultraviolettes\\FluxDataTable\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    /**
     * Flux, Flux Pro et blaze sont enregistrés pour que les balises
     * `<flux:…>` des vues du package soient réellement compilées et rendues
     * pendant les tests. Sans eux, elles ressortent en texte brut : le suite
     * ne voyait donc passer aucune erreur de compilation Blade (cf. #42, où
     * `{{ $bag }}` dans une balise Flux cassait toutes les tables des
     * applications sans faire tomber un seul test ici).
     */
    protected function getPackageProviders($app): array
    {
        return [
            FluxDataTableServiceProvider::class,
            LivewireServiceProvider::class,
            BlazeServiceProvider::class,
            FluxServiceProvider::class,
            FluxProServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        // Fix: Some tests render views that rely on encryption (e.g. @csrf),
        // so we must provide an application key for Orchestra Testbench.
        // We generate a fresh random key for the in-memory test application.
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));

        config()->set('database.default', 'testing');
        // On charge seulement les migrations dédiées aux tests pour ne rien imposer aux applications consommatrices du package
        $migrationsPath = __DIR__.'/database/migrations';
        if (is_dir($migrationsPath)) {
            foreach (File::allFiles($migrationsPath) as $migration) {
                (include $migration->getRealPath())->up();
            }
        }
    }
}
