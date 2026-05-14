<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('project_name')->default('My Project');
            $table->string('site_name')->default('My Website');
            $table->string('tagline')->nullable();

            // Branding
            $table->string('logo')->nullable();
            $table->string('logo_dark')->nullable();
            $table->string('favicon')->nullable();
            $table->string('apple_touch_icon')->nullable();

            // Contact
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();

            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('youtube_url')->nullable();
            $table->string('tiktok_url')->nullable();

            // YouTube Live
            $table->string('youtube_channel_id')->nullable();
            $table->boolean('youtube_live_auto_post_enabled')->default(false);
            $table->string('youtube_live_embed_url')->nullable();

            // TikTok Live
            $table->boolean('tiktok_live_auto_post_enabled')->default(false);
            $table->string('tiktok_live_embed_url')->nullable();

            // GitHub Widget
            $table->string('github_username')->nullable();
            $table->boolean('github_widget_enabled')->default(false);

            // Spotify
            $table->string('spotify_client_id')->nullable();
            $table->string('spotify_client_secret')->nullable();
            $table->text('spotify_refresh_token')->nullable();
            $table->boolean('spotify_widget_enabled')->default(false);

            // Appearance
            $table->string('navbar_layout')->default('classic');
            $table->string('footer_layout')->default('classic');
            $table->boolean('hero_ab_testing_enabled')->default(false);

            // Communication
            $table->string('recaptcha_site_key')->nullable();
            $table->string('recaptcha_secret_key')->nullable();
            $table->string('contact_notify_email')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // AI
            $table->string('gemini_api_key')->nullable();
            $table->string('openrouter_api_key')->nullable();
            $table->string('ai_provider')->nullable();

            // Chatbot
            $table->boolean('chatbot_enabled')->default(false);
            $table->string('chatbot_welcome_message')->nullable();
            $table->text('chatbot_system_prompt')->nullable();

            // Legal
            $table->text('privacy_content')->nullable();
            $table->text('terms_content')->nullable();
            $table->boolean('cookie_consent_enabled')->default(false);
            $table->string('cookie_consent_text')->nullable();

            // Maintenance
            $table->boolean('maintenance_mode')->default(false);
            $table->json('maintenance_pages')->nullable();

            // Misc
            $table->string('canva_api_key')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
