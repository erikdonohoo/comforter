<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * AddCommitRequest class
 *
 * @property string $branch
 * @property string $sha
 * @property int $project_id
 * @property string $coverage
 * @property UploadedFile $zip
 * @property UploadedFile $lcov
 */
class AddCommitRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'branch' => ['required', 'string'],
            'sha' => ['required', 'string'],
            'project_id' => ['required', 'integer'],
            'project_name' => ['string'],
            'zip' => ['required', 'file'],
            'coverage' => ['required_without:lcov', 'numeric'],
            'lcov' => ['required_without:coverage', 'file']
        ];
    }
}
