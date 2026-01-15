import { queryParams, type QueryParams } from './../../../wayfinder'
import get from './get'
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
const regions = {
    index,
get,
}

export default regions