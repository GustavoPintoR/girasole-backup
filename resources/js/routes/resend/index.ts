import { queryParams, type QueryParams } from './../../wayfinder'
/**
* @see \Resend\Laravel\Http\Controllers\WebhookController::webhook
 * @see vendor/resend/resend-laravel/src/Http/Controllers/WebhookController.php:39
 * @route '/resend/webhook'
 */
export const webhook = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: webhook.url(options),
    method: 'post',
})

webhook.definition = {
    methods: ['post'],
    url: '/resend/webhook',
}

/**
* @see \Resend\Laravel\Http\Controllers\WebhookController::webhook
 * @see vendor/resend/resend-laravel/src/Http/Controllers/WebhookController.php:39
 * @route '/resend/webhook'
 */
webhook.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return webhook.definition.url + queryParams(options)
}

/**
* @see \Resend\Laravel\Http\Controllers\WebhookController::webhook
 * @see vendor/resend/resend-laravel/src/Http/Controllers/WebhookController.php:39
 * @route '/resend/webhook'
 */
webhook.post = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: webhook.url(options),
    method: 'post',
})
const resend = {
    webhook,
}

export default resend