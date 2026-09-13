<?php

/**
 * Vaccinations — web routes (Prefix 'vaccinations' aus config).
 */

use Platform\Vaccinations\Livewire\Index as VaccinationIndex;

Route::get('/', VaccinationIndex::class)->name('vaccinations.index');
