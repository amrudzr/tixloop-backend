<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Wraps authentication response data (token + user).
 *
 * @property-read string $token
 * @property-read User $user
 */
class AuthResource extends JsonResource
{
    /**
     * Create a new resource instance.
     *
     * @param  array{user: User, token: string}  $resource
     */
    public function __construct(mixed $resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource['token'],
            'token_type' => 'Bearer',
            'user' => new UserResource($this->resource['user']),
        ];
    }
}
