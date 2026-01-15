import { queryParams, type QueryParams } from './../../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\RegionController::index
 * @see app/Http/Controllers/Api/RegionController.php:15
 * @route '/api/regions'
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
    url: '/api/regions',
}

/**
* @see \App\Http\Controllers\Api\RegionController::index
 * @see app/Http/Controllers/Api/RegionController.php:15
 * @route '/api/regions'
 */
index.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\RegionController::index
 * @see app/Http/Controllers/Api/RegionController.php:15
 * @route '/api/regions'
 */
index.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\RegionController::index
 * @see app/Http/Controllers/Api/RegionController.php:15
 * @route '/api/regions'
 */
index.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Api\RegionController::getProvinces
 * @see app/Http/Controllers/Api/RegionController.php:73
 * @route '/api/regions/{region}/provinces'
 */
export const getProvinces = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: getProvinces.url(args, options),
    method: 'get',
})

getProvinces.definition = {
    methods: ['get','head'],
    url: '/api/regions/{region}/provinces',
}

/**
* @see \App\Http\Controllers\Api\RegionController::getProvinces
 * @see app/Http/Controllers/Api/RegionController.php:73
 * @route '/api/regions/{region}/provinces'
 */
getProvinces.url = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { region: args }
    }

            if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
            args = { region: args.id }
        }
    
    if (Array.isArray(args)) {
        args = {
                    region: args[0],
                }
    }

    const parsedArgs = {
                        region: typeof args.region === 'object'
                ? args.region.id
                : args.region,
                }

    return getProvinces.definition.url
            .replace('{region}', parsedArgs.region.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\RegionController::getProvinces
 * @see app/Http/Controllers/Api/RegionController.php:73
 * @route '/api/regions/{region}/provinces'
 */
getProvinces.get = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: getProvinces.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\RegionController::getProvinces
 * @see app/Http/Controllers/Api/RegionController.php:73
 * @route '/api/regions/{region}/provinces'
 */
getProvinces.head = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: getProvinces.url(args, options),
    method: 'head',
})
const RegionController = { index, getProvinces }

export default RegionController