<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller;

/**
 * Base Controller class for all API controllers.
 * 
 * Provides foundational functionality including authorization,
 * request validation, and standardized API response formatting.
 * All application controllers should extend this base class.
 *
 * @package App\Http\Controllers
 */
class BaseController extends Controller
{
    use AuthorizesRequests, ValidatesRequests, ApiResponse;
}