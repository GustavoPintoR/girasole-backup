import { queryParams, type QueryParams } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\RegionController::provinces
 * @see app/Http/Controllers/Api/RegionController.php:73
 * @route '/api/regions/{region}/provinces'
 */
export const provinces = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: provinces.url(args, options),
    method: 'get',
})

provinces.definition = {
    methods: ['get','head'],
    url: '/api/regions/{region}/provinces',
}

/**
* @see \App\Http\Controllers\Api\RegionController::provinces
 * @see app/Http/Controllers/Api/RegionController.php:73
 * @route '/api/regions/{region}/provinces'
 */
provinces.url = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
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

    return provinces.definition.url
            .replace('{region}', parsedArgs.region.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\RegionController::provinces
 * @see app/Http/Controllers/Api/RegionController.php:73
 * @route '/api/regions/{region}/provinces'
 */
provinces.get = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: provinces.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\RegionController::provinces
 * @see app/Http/Controllers/Api/RegionController.php:73
 * @route '/api/regions/{region}/provinces'
 */
provinces.head = (args: { region: number | { id: number } } | [region: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: provinces.url(args, options),
    method: 'head',
})
const get = {
    provinces,
}

export default get