<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Serp Agent — Custom API integration
    |--------------------------------------------------------------------------
    |
    | Serp Agent (app.serp-agent.com) posts generated articles to our webhook.
    | The URL that has to be entered in the "Custom API" modal is:
    |
    |     https://<domain>/api/serp-agent/articles
    |
    | See routes/api.php for the route definition.
    |
    */

    'enabled' => (bool) env('SERP_AGENT_ENABLED', true),

    /*
     * The "Secret Key" generated in the Custom API modal. Serp Agent sends it
     * as "Authorization: Bearer whsec_...".
     */
    'secret' => env('SERP_AGENT_WEBHOOK_SECRET'),

    /*
     * E-mail of the user that incoming articles are attributed to
     * (blog_articles.creator_id). Falls back to the first admin.
     */
    'author_email' => env('SERP_AGENT_AUTHOR_EMAIL'),

    /*
     * Locale the incoming articles are written in when the payload carries
     * no explicit "locale" field.
     */
    'default_locale' => env('SERP_AGENT_DEFAULT_LOCALE', 'uk'),

    /*
     * Copy the received text into every other site language as well. The blog
     * renders article blocks per locale without a fallback, so with this off
     * an article received in "uk" would show an empty body on /ru.
     */
    'mirror_to_other_locales' => (bool) env('SERP_AGENT_MIRROR_LOCALES', true),

    /*
     * blog_articles.hero_image_path is NOT NULL. When a payload carries no
     * image we fall back to this path on the public disk, e.g.
     * "application-images/serp-agent-default.webp". Leave empty to reject
     * imageless articles instead.
     */
    'default_hero_image' => env('SERP_AGENT_DEFAULT_HERO_IMAGE'),

    'image' => [
        'timeout' => (int) env('SERP_AGENT_IMAGE_TIMEOUT', 20),
        'max_bytes' => (int) env('SERP_AGENT_IMAGE_MAX_BYTES', 10485760),
    ],

    /*
     * Append the "faq" / "relatedArticles" / "recommendedResources" parts of
     * the payload to the article body. The blog template renders only text,
     * image and video blocks, so these have to be part of the HTML to show up.
     */
    'append_faq' => (bool) env('SERP_AGENT_APPEND_FAQ', true),
    'append_related' => (bool) env('SERP_AGENT_APPEND_RELATED', true),

    /*
     * "Save & Test" in the Serp Agent panel does not send an empty ping — it
     * sends a whole demo article whose image URL is a placeholder that answers
     * 401. Deliveries carrying one of these slugs are acknowledged with a 200
     * and deliberately not published.
     */
    'test_slugs' => [
        'test-article-serp-agent',
    ],

    /*
     * Demote <h1> inside the received body to <h2>. The article template
     * already prints the article name as the page <h1>.
     */
    'demote_h1' => (bool) env('SERP_AGENT_DEMOTE_H1', true),
];
