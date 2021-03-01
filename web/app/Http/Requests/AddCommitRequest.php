<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;

/**
 * AddCommitRequest class
 *
 * @property string $branch
 * @property string $commit
 * @property int $project
 * @property string $coverage
 * @property string $name
 * @property string $mergeBase
 * @property string $mergeRequestIID
 * @property int $totalLines
 * @property int $totalCovered
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
            'commit' => ['required', 'string'],
            'project' => ['required', 'integer'],
            'name' => ['string'],
            'zip' => ['required', 'file'],
            'mergeBase' => ['string'],
            'mergeRequestIID' => ['string'],
            'coverage' => ['required_without:lcov', 'numeric'],
            'lcov' => ['required_without:coverage', 'file'],
            'totalLines' => ['required_without:lcov', 'numeric'],
            'totalCovered' => ['required_without:lcov', 'numeric']
        ];
    }
}
