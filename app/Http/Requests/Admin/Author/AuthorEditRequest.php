<?php

namespace App\Http\Requests\Admin\Author;

use App\Http\Requests\BaseRequest;
use App\Models\Author;
use App\Services\Author\DTO\EditAuthorDTO;
use Illuminate\Validation\Rule;

class AuthorEditRequest extends BaseRequest
{
    public function baseRules(): array
    {
        $author = $this->route('author');

        $rules = [
            'name' => ['array'],
            'job_title' => ['array'],
            'short_description' => ['array'],
            'biography' => ['array'],
            'meta_title' => ['array'],
            'meta_description' => ['array'],
            'meta_keywords' => ['array'],

            'slug' => [
                'required',
                'string',
                'max:191',
                Rule::unique('authors', 'slug')->ignore($author instanceof Author ? $author->id : null),
            ],

            'photo' => ['nullable', 'image', 'max:10240'],

            'instagram_url' => ['nullable', 'string', 'url', 'max:2048'],
            'facebook_url' => ['nullable', 'string', 'url', 'max:2048'],
            'linkedin_url' => ['nullable', 'string', 'url', 'max:2048'],

            'certificate' => ['nullable', 'array'],
            'certificate.*.id' => ['nullable', 'integer'],
            'certificate.*.image' => ['nullable', 'image', 'max:10240'],
            'certificate.*.issuer' => ['nullable', 'string', 'max:191'],
            // Bounds of the MySQL YEAR column the value is stored in.
            'certificate.*.issued_year' => ['nullable', 'integer', 'min:1901', 'max:2155'],
        ];

        foreach ($this->availableLanguages as $availableLanguage) {
            $rules['name.'.$availableLanguage] = ['required', 'string', 'max:191'];
            $rules['job_title.'.$availableLanguage] = ['nullable', 'string', 'max:191'];
            $rules['short_description.'.$availableLanguage] = ['nullable', 'string', 'max:500'];
            $rules['biography.'.$availableLanguage] = ['nullable', 'string'];
            $rules['meta_title.'.$availableLanguage] = ['nullable', 'string', 'max:255'];
            $rules['meta_description.'.$availableLanguage] = ['nullable', 'string', 'max:1000'];
            $rules['meta_keywords.'.$availableLanguage] = ['nullable', 'string', 'max:1000'];
            $rules['certificate.*.title.'.$availableLanguage] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    public function toDTO(): EditAuthorDTO
    {
        return new EditAuthorDTO(
            name: $this->input('name', []),
            slug: $this->input('slug'),
            jobTitle: $this->input('job_title'),
            shortDescription: $this->input('short_description'),
            biography: $this->input('biography'),
            photo: $this->file('photo'),
            instagramUrl: $this->input('instagram_url'),
            facebookUrl: $this->input('facebook_url'),
            linkedinUrl: $this->input('linkedin_url'),
            metaTitle: $this->input('meta_title'),
            metaDescription: $this->input('meta_description'),
            metaKeywords: $this->input('meta_keywords'),
            certificates: $this->buildCertificates(),
        );
    }

    /**
     * Merges the uploaded files back into the plain input, so the service gets
     * one list instead of two parallel ones.
     */
    private function buildCertificates(): ?array
    {
        $certificates = $this->input('certificate');

        if (! is_array($certificates)) {
            return null;
        }

        foreach ($certificates as $index => $certificate) {
            $image = $this->file('certificate.'.$index.'.image');

            if ($image) {
                $certificates[$index]['image'] = $image;
            } else {
                unset($certificates[$index]['image']);
            }
        }

        return $certificates;
    }
}
