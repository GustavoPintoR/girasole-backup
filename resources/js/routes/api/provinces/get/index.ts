import { queryParams, type QueryParams } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\Api\ProvinceController::cities
 * @see app/Http/Controllers/Api/ProvinceController.php:71
 * @route '/api/provinces/{province}/cities'
 */
export const cities = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: cities.url(args, options),
    method: 'get',
})

cities.definition = {
    methods: ['get','head'],
    url: '/api/provinces/{province}/cities',
}

/**
* @see \App\Http\Controllers\Api\ProvinceController::cities
 * @see app/Http/Controllers/Api/ProvinceController.php:71
 * @route '/api/provinces/{province}/cities'
 */
cities.url = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
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

    return cities.definition.url
            .replace('{province}', parsedArgs.province.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Api\ProvinceController::cities
 * @see app/Http/Controllers/Api/ProvinceController.php:71
 * @route '/api/provinces/{province}/cities'
 */
cities.get = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: cities.url(args, options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\Api\ProvinceController::cities
 * @see app/Http/Controllers/Api/ProvinceController.php:71
 * @route '/api/provinces/{province}/cities'
 */
cities.head = (args: { province: number | { id: number } } | [province: number | { id: number } ] | number | { id: number }, options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: cities.url(args, options),
    method: 'head',
})
const get = {
    cities,
}

export default get