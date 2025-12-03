<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PartnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'partner_type' => $this->partner_type,
            'name' => $this->name,
            'trade_name' => $this->trade_name,
            'tax_id' => $this->tax_id,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'ubigeo_code' => $this->ubigeo_code,
            'is_customer' => $this->is_customer,
            'is_supplier' => $this->is_supplier,
            'payment_terms_days' => $this->payment_terms_days,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'roles' => $this->getRolesLabel(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get formatted roles label
     */
    private function getRolesLabel(): string
    {
        $roles = [];
        if ($this->is_customer) $roles[] = 'Customer';
        if ($this->is_supplier) $roles[] = 'Supplier';
        return implode(' & ', $roles) ?: 'None';
    }
}
