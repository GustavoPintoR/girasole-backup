import { queryParams, type QueryParams } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\PostalCodeController::index
 * @see app/Http/Controllers/PostalCodeController.php:15
 * @route '/postal-codes'
 */
export const index = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ['get','head'],
    url: '/postal-codes',
}

/**
* @see \App\Http\Controllers\PostalCodeController::index
 * @see app/Http/Controllers/PostalCodeController.php:15
 * @route '/postal-codes'
 */
index.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostalCodeController::index
 * @see app/Http/Controllers/PostalCodeController.php:15
 * @route '/postal-codes'
 */
index.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PostalCodeController::index
 * @see app/Http/Controllers/PostalCodeController.php:15
 * @route '/postal-codes'
 */
index.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostalCodeController::create
 * @see app/Http/Controllers/PostalCodeController.php:60
 * @route '/postal-codes/create'
 */
export const create = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ['get','head'],
    url: '/postal-codes/create',
}

/**
* @see \App\Http\Controllers\PostalCodeController::create
 * @see app/Http/Controllers/PostalCodeController.php:60
 * @route '/postal-codes/create'
 */
create.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostalCodeController::create
 * @see app/Http/Controllers/PostalCodeController.php:60
 * @route '/postal-codes/create'
 */
create.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PostalCodeController::create
 * @see app/Http/Controllers/PostalCodeController.php:60
 * @route '/postal-codes/create'
 */
create.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostalCodeController::store
 * @see app/Http/Controllers/PostalCodeController.php:68
 * @route '/postal-codes'
 */
export const store = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ['post'],
    url: '/postal-codes',
}

/**
* @see \App\Http\Controllers\PostalCodeController::store
 * @see app/Http/Controllers/PostalCodeController.php:68
 * @route '/postal-codes'
 */
store.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostalCodeController::store
 * @see app/Http/Controllers/PostalCodeController.php:68
 * @route '/postal-codes'
 */
store.post = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\PostalCodeController::show
 * @see app/Http/Controllers/PostalCodeController.php:102
 * @route '/postal-codes/{postal_code}'
 */
export const show = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ['get','head'],
    url: '/postal-codes/{postal_code}',
}

/**
* @see \App\Http\Controllers\PostalCodeController::show
 * @see app/Http/Controllers/PostalCodeController.php:102
 * @route '/postal-codes/{postal_code}'
 */
show.url = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { postal_code: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    postal_code: args[0],
                }
    }

    const parsedArgs = {
                        postal_code: args.postal_code,
                }

    return show.definition.url
            .replace('{postal_code}', parsedArgs.postal_code.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostalCodeController::show
 * @see app/Http/Controllers/PostalCodeController.php:102
 * @route '/postal-codes/{postal_code}'
 */
show.get = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PostalCodeController::show
 * @see app/Http/Controllers/PostalCodeController.php:102
 * @route '/postal-codes/{postal_code}'
 */
show.head = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostalCodeController::edit
 * @see app/Http/Controllers/PostalCodeController.php:113
 * @route '/postal-codes/{postal_code}/edit'
 */
export const edit = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ['get','head'],
    url: '/postal-codes/{postal_code}/edit',
}

/**
* @see \App\Http\Controllers\PostalCodeController::edit
 * @see app/Http/Controllers/PostalCodeController.php:113
 * @route '/postal-codes/{postal_code}/edit'
 */
edit.url = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { postal_code: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    postal_code: args[0],
                }
    }

    const parsedArgs = {
                        postal_code: args.postal_code,
                }

    return edit.definition.url
            .replace('{postal_code}', parsedArgs.postal_code.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostalCodeController::edit
 * @see app/Http/Controllers/PostalCodeController.php:113
 * @route '/postal-codes/{postal_code}/edit'
 */
edit.get = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\PostalCodeController::edit
 * @see app/Http/Controllers/PostalCodeController.php:113
 * @route '/postal-codes/{postal_code}/edit'
 */
edit.head = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\PostalCodeController::update
 * @see app/Http/Controllers/PostalCodeController.php:123
 * @route '/postal-codes/{postal_code}'
 */
export const update = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'put',
} => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ['put','patch'],
    url: '/postal-codes/{postal_code}',
}

/**
* @see \App\Http\Controllers\PostalCodeController::update
 * @see app/Http/Controllers/PostalCodeController.php:123
 * @route '/postal-codes/{postal_code}'
 */
update.url = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { postal_code: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    postal_code: args[0],
                }
    }

    const parsedArgs = {
                        postal_code: args.postal_code,
                }

    return update.definition.url
            .replace('{postal_code}', parsedArgs.postal_code.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostalCodeController::update
 * @see app/Http/Controllers/PostalCodeController.php:123
 * @route '/postal-codes/{postal_code}'
 */
update.put = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'put',
} => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\PostalCodeController::update
 * @see app/Http/Controllers/PostalCodeController.php:123
 * @route '/postal-codes/{postal_code}'
 */
update.patch = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'patch',
} => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\PostalCodeController::destroy
 * @see app/Http/Controllers/PostalCodeController.php:160
 * @route '/postal-codes/{postal_code}'
 */
export const destroy = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'delete',
} => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ['delete'],
    url: '/postal-codes/{postal_code}',
}

/**
* @see \App\Http\Controllers\PostalCodeController::destroy
 * @see app/Http/Controllers/PostalCodeController.php:160
 * @route '/postal-codes/{postal_code}'
 */
destroy.url = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { postal_code: args }
    }

    
    if (Array.isArray(args)) {
        args = {
                    postal_code: args[0],
                }
    }

    const parsedArgs = {
                        postal_code: args.postal_code,
                }

    return destroy.definition.url
            .replace('{postal_code}', parsedArgs.postal_code.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\PostalCodeController::destroy
 * @see app/Http/Controllers/PostalCodeController.php:160
 * @route '/postal-codes/{postal_code}'
 */
destroy.delete = (args: { postal_code: string | number } | [postal_code: string | number ] | string | number, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'delete',
} => ({
    url: destroy.url(args, options),
    method: 'delete',
})
const PostalCodeController = { index, create, store, show, edit, update, destroy }

export default PostalCodeController