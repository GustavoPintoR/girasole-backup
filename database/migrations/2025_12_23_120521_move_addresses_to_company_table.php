<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Billing Addresses
        Schema::table('billing_addresses', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('legacy_user_id')->nullable()->after('user_id');
        });

        DB::table('billing_addresses')->update(['legacy_user_id' => DB::raw('user_id')]);

        $billingAddresses = DB::table('billing_addresses')->get();
        foreach ($billingAddresses as $address) {
            if ($address->user_id) {
                $company = DB::table('companies')->where('owner_id', $address->user_id)->first();
                if ($company) {
                    DB::table('billing_addresses')
                        ->where('id', $address->id)
                        ->update(['company_id' => $company->id]);
                }
            }
        }

        Schema::table('billing_addresses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        // Shipping Addresses
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('legacy_user_id')->nullable()->after('user_id');
        });

        DB::table('shipping_addresses')->update(['legacy_user_id' => DB::raw('user_id')]);

        $shippingAddresses = DB::table('shipping_addresses')->get();
        foreach ($shippingAddresses as $address) {
            if ($address->user_id) {
                $company = DB::table('companies')->where('owner_id', $address->user_id)->first();
                if ($company) {
                    DB::table('shipping_addresses')
                        ->where('id', $address->id)
                        ->update(['company_id' => $company->id]);
                }
            }
        }

        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Billing Addresses
        Schema::table('billing_addresses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate();
        });

        DB::table('billing_addresses')
            ->whereNotNull('legacy_user_id')
            ->update(['user_id' => DB::raw('legacy_user_id')]);

        // Try to recover user_id from company owner if legacy is missing
        $billingAddresses = DB::table('billing_addresses')->whereNull('user_id')->whereNotNull('company_id')->get();
        foreach ($billingAddresses as $address) {
             $company = DB::table('companies')->where('id', $address->company_id)->first();
             if ($company && $company->owner_id) {
                 DB::table('billing_addresses')
                     ->where('id', $address->id)
                     ->update(['user_id' => $company->owner_id]);
             }
        }

        Schema::table('billing_addresses', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->dropColumn('legacy_user_id');
        });

        // Shipping Addresses
        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate();
        });

        DB::table('shipping_addresses')
            ->whereNotNull('legacy_user_id')
            ->update(['user_id' => DB::raw('legacy_user_id')]);

        // Try to recover user_id from company owner if legacy is missing
        $shippingAddresses = DB::table('shipping_addresses')->whereNull('user_id')->whereNotNull('company_id')->get();
        foreach ($shippingAddresses as $address) {
             $company = DB::table('companies')->where('id', $address->company_id)->first();
             if ($company && $company->owner_id) {
                 DB::table('shipping_addresses')
                     ->where('id', $address->id)
                     ->update(['user_id' => $company->owner_id]);
             }
        }

        Schema::table('shipping_addresses', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
            $table->dropColumn('legacy_user_id');
        });
    }
};
