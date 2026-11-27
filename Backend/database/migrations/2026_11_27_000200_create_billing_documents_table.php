<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Devis (quotes) and factures (invoices) issued from the admin. Each one is
     * a self-contained snapshot: client and lines are stored as entered, so a
     * later change to a car's price never alters an issued document.
     */
    public function up(): void
    {
        Schema::create('billing_documents', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index();
            $table->string('number')->unique();
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('sequence');
            $table->date('issue_date');
            $table->unsignedSmallInteger('validity_days')->nullable();
            $table->string('client_name');
            $table->text('client_details')->nullable();
            $table->json('lines');
            $table->decimal('tva_rate', 5, 2)->default(20);
            $table->decimal('total_ht', 12, 2)->default(0);
            $table->decimal('total_tva', 12, 2)->default(0);
            $table->decimal('total_ttc', 12, 2)->default(0);
            $table->text('notes')->nullable();
            // The devis a facture was converted from.
            $table->foreignId('source_document_id')->nullable()->constrained('billing_documents')->nullOnDelete();
            $table->timestamps();

            $table->unique(['type', 'year', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_documents');
    }
};
