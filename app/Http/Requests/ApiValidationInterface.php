<?php
/**
 * General API validation interface to keep consistent code structure
*/

namespace App\Http\Requests;


use Illuminate\Http\Request;

interface ApiValidationInterface
{
    function authorize(Request $request);

    function failedAuthorization();

    function rules();

    function response(array $errors);
}