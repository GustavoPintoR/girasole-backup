import { queryParams, type QueryParams } from './../wayfinder'
/**
 * @see vendor/laravel/pulse/src/PulseServiceProvider.php:106
 * @route '/pulse'
 */
export const pulse = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: pulse.url(options),
    method: 'get',
})

pulse.definition = {
    methods: ['get','head'],
    url: '/pulse',
}

/**
 * @see vendor/laravel/pulse/src/PulseServiceProvider.php:106
 * @route '/pulse'
 */
pulse.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return pulse.definition.url + queryParams(options)
}

/**
 * @see vendor/laravel/pulse/src/PulseServiceProvider.php:106
 * @route '/pulse'
 */
pulse.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: pulse.url(options),
    method: 'get',
})
/**
 * @see vendor/laravel/pulse/src/PulseServiceProvider.php:106
 * @route '/pulse'
 */
pulse.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: pulse.url(options),
    method: 'head',
})

/**
 * @see routes/web.php:15
 * @route '/'
 */
export const home = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: home.url(options),
    method: 'get',
})

home.definition = {
    methods: ['get','head'],
    url: '/',
}

/**
 * @see routes/web.php:15
 * @route '/'
 */
home.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return home.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:15
 * @route '/'
 */
home.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: home.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:15
 * @route '/'
 */
home.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: home.url(options),
    method: 'head',
})

/**
 * @see routes/settings.php:21
 * @route '/settings/appearance'
 */
export const appearance = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: appearance.url(options),
    method: 'get',
})

appearance.definition = {
    methods: ['get','head'],
    url: '/settings/appearance',
}

/**
 * @see routes/settings.php:21
 * @route '/settings/appearance'
 */
appearance.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return appearance.definition.url + queryParams(options)
}

/**
 * @see routes/settings.php:21
 * @route '/settings/appearance'
 */
appearance.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: appearance.url(options),
    method: 'get',
})
/**
 * @see routes/settings.php:21
 * @route '/settings/appearance'
 */
appearance.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: appearance.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:24
 * @route '/register'
 */
export const register = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: register.url(options),
    method: 'get',
})

register.definition = {
    methods: ['get','head'],
    url: '/register',
}

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:24
 * @route '/register'
 */
register.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return register.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:24
 * @route '/register'
 */
register.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: register.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\RegisteredUserController::register
 * @see app/Http/Controllers/Auth/RegisteredUserController.php:24
 * @route '/register'
 */
register.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: register.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:19
 * @route '/login'
 */
export const login = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: login.url(options),
    method: 'get',
})

login.definition = {
    methods: ['get','head'],
    url: '/login',
}

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:19
 * @route '/login'
 */
login.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return login.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:19
 * @route '/login'
 */
login.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: login.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::login
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:19
 * @route '/login'
 */
login.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: login.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:42
 * @route '/logout'
 */
export const logout = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: logout.url(options),
    method: 'post',
})

logout.definition = {
    methods: ['post'],
    url: '/logout',
}

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:42
 * @route '/logout'
 */
logout.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return logout.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\AuthenticatedSessionController::logout
 * @see app/Http/Controllers/Auth/AuthenticatedSessionController.php:42
 * @route '/logout'
 */
logout.post = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: logout.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Auth\WaitingController::waiting
 * @see app/Http/Controllers/Auth/WaitingController.php:10
 * @route '/waiting'
 */
export const waiting = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: waiting.url(options),
    method: 'get',
})

waiting.definition = {
    methods: ['get','head'],
    url: '/waiting',
}

/**
* @see \App\Http\Controllers\Auth\WaitingController::waiting
 * @see app/Http/Controllers/Auth/WaitingController.php:10
 * @route '/waiting'
 */
waiting.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return waiting.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Auth\WaitingController::waiting
 * @see app/Http/Controllers/Auth/WaitingController.php:10
 * @route '/waiting'
 */
waiting.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: waiting.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Auth\WaitingController::waiting
 * @see app/Http/Controllers/Auth/WaitingController.php:10
 * @route '/waiting'
 */
waiting.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: waiting.url(options),
    method: 'head',
})

/**
 * @see routes/web.php:37
 * @route '/dashboard'
 */
export const dashboard = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: dashboard.url(options),
    method: 'get',
})

dashboard.definition = {
    methods: ['get','head'],
    url: '/dashboard',
}

/**
 * @see routes/web.php:37
 * @route '/dashboard'
 */
dashboard.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return dashboard.definition.url + queryParams(options)
}

/**
 * @see routes/web.php:37
 * @route '/dashboard'
 */
dashboard.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: dashboard.url(options),
    method: 'get',
})
/**
 * @see routes/web.php:37
 * @route '/dashboard'
 */
dashboard.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: dashboard.url(options),
    method: 'head',
})