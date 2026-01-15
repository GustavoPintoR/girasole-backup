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
        Schema::table('billing_infos', function (Blueprint $table) {
            $table->foreignId('company_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // preserve original user_id
        Schema::table('billing_infos', function (Blueprint $table) {
            $table->unsignedBigInteger('legacy_user_id')->nullable()->after('user_id');
        });

        // copy user_id values into legacy_user_id
        DB::table('billing_infos')->update(['legacy_user_id' => DB::raw('user_id')]);

        $billingInfos = DB::table('billing_infos')->get();
        foreach ($billingInfos as $info) {
            if ($info->user_id) {
                // Find a company owned by this user
                $company = DB::table('companies')->where('owner_id', $info->user_id)->first();

                // // If not owner, find a company where the user is a member
                // if (!$company) {
                //     $companyUser = DB::table('company_user')->where('user_id', $info->user_id)->first();
                //     if ($companyUser) {
                //         $company = DB::table('companies')->where('id', $companyUser->company_id)->first();
                //     }
                // }

                if ($company) {
                    DB::table('billing_infos')
                        ->where('id', $info->id)
                        ->update(['company_id' => $company->id]);
                }
            }
        }

        Schema::table('billing_infos', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billing_infos', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnUpdate();
        });

        // Restore data from preserved legacy_user_id when available
        DB::table('billing_infos')
            ->whereNotNull('legacy_user_id')
            ->update(['user_id' => DB::raw('legacy_user_id')]);

        // records without legacy_user_id, use company owner
        $billingInfos = DB::table('billing_infos')->whereNull('user_id')->whereNotNull('company_id')->get();
        foreach ($billingInfos as $info) {
            $company = DB::table('companies')->where('id', $info->company_id)->first();
            if ($company && $company->owner_id) {
                DB::table('billing_infos')
                    ->where('id', $info->id)
                    ->update(['user_id' => $company->owner_id]);
            }
        }

        Schema::table('billing_infos', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropColumn('company_id');
        });

        Schema::table('billing_infos', function (Blueprint $table) {
            $table->dropColumn('legacy_user_id');
        });
    }
};
