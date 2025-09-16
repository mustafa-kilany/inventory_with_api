<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'employee_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }


    // Relationships
    public function purchaseRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'requested_by');
    }

    public function approvedRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'approved_by');
    }

    public function fulfilledRequests(): HasMany
    {
        return $this->hasMany(PurchaseRequest::class, 'fulfilled_by');
    }

    public function stockTransactions(): HasMany
    {
        return $this->hasMany(StockTransaction::class, 'performed_by');
    }

    // Filament Panel Access Control
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->canManageUsers() || $this->canManageStock() || $this->canApprove();
    }

    // Override role checking methods to use Spatie permissions
    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }

    public function isApprover(): bool
    {
        return $this->hasRole('approver');
    }

    public function isStockKeeper(): bool
    {
        return $this->hasRole('stock_keeper');
    }

    public function isAdministrator(): bool
    {
        return $this->hasRole('administrator');
    }

    public function canApprove(): bool
    {
        return $this->hasAnyRole(['approver', 'administrator']);
    }

    public function canManageStock(): bool
    {
        return $this->hasAnyRole(['stock_keeper', 'administrator']);
    }

    public function canManageUsers(): bool
    {
        return $this->hasRole('administrator');
    }

    // New workflow role methods
    public function isDepartmentHead(): bool
    {
        return $this->hasRole('department_head');
    }

    public function isManager(): bool
    {
        return $this->hasRole('manager');
    }

    public function isPurchaseDepartment(): bool
    {
        return $this->hasRole('purchase_department');
    }

    // Enhanced permission methods
    public function canApproveAsDepartmentHead(): bool
    {
        return $this->hasAnyRole(['department_head', 'administrator']);
    }

    public function canApproveAsManager(): bool
    {
        return $this->hasAnyRole(['manager', 'administrator']);
    }

    public function canApproveAsPurchaseDepartment(): bool
    {
        return $this->hasAnyRole(['purchase_department', 'administrator']);
    }

    public function canViewStockLevels(): bool
    {
        return $this->hasAnyRole(['stock_keeper', 'administrator', 'department_head', 'manager', 'purchase_department']);
    }
}
