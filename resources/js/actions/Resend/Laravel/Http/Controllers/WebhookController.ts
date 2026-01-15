import { queryParams, type QueryParams } from './../../../../../wayfinder'
/**
* @see \Resend\Laravel\Http\Controllers\WebhookController::handleWebhook
 * @see vendor/resend/resend-laravel/src/Http/Controllers/WebhookController.php:39
 * @route '/resend/webhook'
 */
export const handleWebhook = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: handleWebhook.url(options),
    method: 'post',
})

handleWebhook.definition = {
    methods: ['post'],
    url: '/resend/webhook',
}

/**
* @see \Resend\Laravel\Http\Controllers\WebhookController::handleWebhook
 * @see vendor/resend/resend-laravel/src/Http/Controllers/WebhookController.php:39
 * @route '/resend/webhook'
 */
handleWebhook.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return handleWebhook.definition.url + queryParams(options)
}

/**
* @see \Resend\Laravel\Http\Controllers\WebhookController::handleWebhook
 * @see vendor/resend/resend-laravel/src/Http/Controllers/WebhookController.php:39
 * @route '/resend/webhook'
 */
handleWebhook.post = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: handleWebhook.url(options),
    method: 'post',
})
const WebhookController = { handleWebhook }

export default WebhookController