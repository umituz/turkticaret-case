<?php

namespace App\Repositories\Language;

use App\Models\Language\Language;
use App\Repositories\Base\BaseRepository;

/**
 * Repository class for managing language data operations.
 * 
 * This repository extends the BaseRepository to provide language-specific
 * database operations and queries. It includes methods for finding languages
 * by their code and inherits all CRUD operations from the base repository.
 * 
 * @package App\Repositories\Language
 */
class LanguageRepository extends BaseRepository implements LanguageRepositoryInterface
{
    /**
     * Create a new LanguageRepository instance.
     * 
     * @param Language $model The Language model instance to be injected
     */
    public function __construct(Language $model)
    {
        parent::__construct($model);
    }

    /**
     * Find a language by its language code.
     * 
     * This method searches for a language record using the unique language
     * code (e.g., 'en', 'tr', 'fr') and returns the first matching record.
     * 
     * @param string $code The language code to search for
     * @return Language|null The language instance if found, null otherwise
     */
    public function findByCode(string $code): ?Language
    {
        return $this->model->where('code', $code)->first();
    }
}