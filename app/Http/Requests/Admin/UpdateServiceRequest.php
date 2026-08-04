<?php

namespace App\Http\Requests\Admin;

use App\Models\Service;

class UpdateServiceRequest extends ServiceFormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');

        return $service instanceof Service && ($this->user()?->can('update', $service) ?? false);
    }
}
