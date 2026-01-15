import { queryParams, type QueryParams } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\ProvinceController::index
 * @see app/Http/Controllers/ProvinceController.php:21
 * @route '/provinces'
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
    url: '/provinces',
}

/**
* @see \App\Http\Controllers\ProvinceController::index
 * @see app/Http/Controllers/ProvinceController.php:21
 * @route '/provinces'
 */
index.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::index
 * @see app/Http/Controllers/ProvinceController.php:21
 * @route '/provinces'
 */
index.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProvinceController::index
 * @see app/Http/Controllers/ProvinceController.php:21
 * @route '/provinces'
 */
index.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProvinceController::create
 * @see app/Http/Controllers/ProvinceController.php:65
 * @route '/provinces/create'
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
    url: '/provinces/create',
}

/**
* @see \App\Http\Controllers\ProvinceController::create
 * @see app/Http/Controllers/ProvinceController.php:65
 * @route '/provinces/create'
 */
create.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::create
 * @see app/Http/Controllers/ProvinceController.php:65
 * @route '/provinces/create'
 */
create.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: create.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProvinceController::create
 * @see app/Http/Controllers/ProvinceController.php:65
 * @route '/provinces/create'
 */
create.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProvinceController::store
 * @see app/Http/Controllers/ProvinceController.php:77
 * @route '/provinces'
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
    url: '/provinces',
}

/**
* @see \App\Http\Controllers\ProvinceController::store
 * @see app/Http/Controllers/ProvinceController.php:77
 * @route '/provinces'
 */
store.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::store
 * @see app/Http/Controllers/ProvinceController.php:77
 * @route '/provinces'
 */
store.post = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ProvinceController::show
 * @see app/Http/Controllers/ProvinceController.php:100
 * @route '/provinces/{province}'
 */
export const show = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ['get','head'],
    url: '/provinces/{province}',
}

/**
* @see \App\Http\Controllers\ProvinceController::show
 * @see app/Http/Controllers/ProvinceController.php:100
 * @route '/provinces/{province}'
 */
show.url = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { province: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { province: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    province: args[0],
                }
    }

    const parsedArgs = {
                        province: typeof args.province === 'object'
                ? args.province.id
                : args.province,
                }

    return show.definition.url
            .replace('{province}', parsedArgs.province.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::show
 * @see app/Http/Controllers/ProvinceController.php:100
 * @route '/provinces/{province}'
 */
show.get = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: show.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProvinceController::show
 * @see app/Http/Controllers/ProvinceController.php:100
 * @route '/provinces/{province}'
 */
show.head = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProvinceController::edit
 * @see app/Http/Controllers/ProvinceController.php:118
 * @route '/provinces/{province}/edit'
 */
export const edit = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ['get','head'],
    url: '/provinces/{province}/edit',
}

/**
* @see \App\Http\Controllers\ProvinceController::edit
 * @see app/Http/Controllers/ProvinceController.php:118
 * @route '/provinces/{province}/edit'
 */
edit.url = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { province: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { province: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    province: args[0],
                }
    }

    const parsedArgs = {
                        province: typeof args.province === 'object'
                ? args.province.id
                : args.province,
                }

    return edit.definition.url
            .replace('{province}', parsedArgs.province.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::edit
 * @see app/Http/Controllers/ProvinceController.php:118
 * @route '/provinces/{province}/edit'
 */
edit.get = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: edit.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProvinceController::edit
 * @see app/Http/Controllers/ProvinceController.php:118
 * @route '/provinces/{province}/edit'
 */
edit.head = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProvinceController::update
 * @see app/Http/Controllers/ProvinceController.php:135
 * @route '/provinces/{province}'
 */
export const update = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'put',
} => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ['put','patch'],
    url: '/provinces/{province}',
}

/**
* @see \App\Http\Controllers\ProvinceController::update
 * @see app/Http/Controllers/ProvinceController.php:135
 * @route '/provinces/{province}'
 */
update.url = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { province: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { province: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    province: args[0],
                }
    }

    const parsedArgs = {
                        province: typeof args.province === 'object'
                ? args.province.id
                : args.province,
                }

    return update.definition.url
            .replace('{province}', parsedArgs.province.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::update
 * @see app/Http/Controllers/ProvinceController.php:135
 * @route '/provinces/{province}'
 */
update.put = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'put',
} => ({
    url: update.url(args, options),
    method: 'put',
})
/**
* @see \App\Http\Controllers\ProvinceController::update
 * @see app/Http/Controllers/ProvinceController.php:135
 * @route '/provinces/{province}'
 */
update.patch = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'patch',
} => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\ProvinceController::destroy
 * @see app/Http/Controllers/ProvinceController.php:158
 * @route '/provinces/{province}'
 */
export const destroy = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'delete',
} => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ['delete'],
    url: '/provinces/{province}',
}

/**
* @see \App\Http\Controllers\ProvinceController::destroy
 * @see app/Http/Controllers/ProvinceController.php:158
 * @route '/provinces/{province}'
 */
destroy.url = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { province: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { province: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    province: args[0],
                }
    }

    const parsedArgs = {
                        province: typeof args.province === 'object'
                ? args.province.id
                : args.province,
                }

    return destroy.definition.url
            .replace('{province}', parsedArgs.province.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::destroy
 * @see app/Http/Controllers/ProvinceController.php:158
 * @route '/provinces/{province}'
 */
destroy.delete = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'delete',
} => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\ProvinceController::available
 * @see app/Http/Controllers/ProvinceController.php:170
 * @route '/provinces/{province}/available-cities'
 */
export const available = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: available.url(args, options),
    method: 'get',
})

available.definition = {
    methods: ['get','head'],
    url: '/provinces/{province}/available-cities',
}

/**
* @see \App\Http\Controllers\ProvinceController::available
 * @see app/Http/Controllers/ProvinceController.php:170
 * @route '/provinces/{province}/available-cities'
 */
available.url = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { province: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { province: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    province: args[0],
                }
    }

    const parsedArgs = {
                        province: typeof args.province === 'object'
                ? args.province.id
                : args.province,
                }

    return available.definition.url
            .replace('{province}', parsedArgs.province.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::available
 * @see app/Http/Controllers/ProvinceController.php:170
 * @route '/provinces/{province}/available-cities'
 */
available.get = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: available.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ProvinceController::available
 * @see app/Http/Controllers/ProvinceController.php:170
 * @route '/provinces/{province}/available-cities'
 */
available.head = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: available.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ProvinceController::attach
 * @see app/Http/Controllers/ProvinceController.php:185
 * @route '/provinces/{province}/attach-cities'
 */
export const attach = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: attach.url(args, options),
    method: 'post',
})

attach.definition = {
    methods: ['post'],
    url: '/provinces/{province}/attach-cities',
}

/**
* @see \App\Http\Controllers\ProvinceController::attach
 * @see app/Http/Controllers/ProvinceController.php:185
 * @route '/provinces/{province}/attach-cities'
 */
attach.url = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { province: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { province: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    province: args[0],
                }
    }

    const parsedArgs = {
                        province: typeof args.province === 'object'
                ? args.province.id
                : args.province,
                }

    return attach.definition.url
            .replace('{province}', parsedArgs.province.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\ProvinceController::attach
 * @see app/Http/Controllers/ProvinceController.php:185
 * @route '/provinces/{province}/attach-cities'
 */
attach.post = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: attach.url(args, options),
    method: 'post',
})
const ProvinceController = { index, create, store, show, edit, update, destroy, available, attach }

export default ProvinceController