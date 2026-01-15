import { queryParams, type QueryParams } from './../../wayfinder'

/**
* @see \App\Http\Controllers\ImportController::index
 * @see app/Http/Controllers/ImportController.php:17
 * @route '/imports'
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
    url: '/imports',
}

/**
* @see \App\Http\Controllers\ImportController::index
 * @see app/Http/Controllers/ImportController.php:17
 * @route '/imports'
 */
index.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ImportController::index
 * @see app/Http/Controllers/ImportController.php:17
 * @route '/imports'
 */
index.get = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'get',
} => ({
    url: index.url(options),
    method: 'get',
})
/**
* @see \App\Http\Controllers\ImportController::index
 * @see app/Http/Controllers/ImportController.php:17
 * @route '/imports'
 */
index.head = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'head',
} => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\ImportController::regions
 * @see app/Http/Controllers/ImportController.php:28
 * @route '/imports/regions'
 */
export const regions = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: regions.url(options),
    method: 'post',
})

regions.definition = {
    methods: ['post'],
    url: '/imports/regions',
}

/**
* @see \App\Http\Controllers\ImportController::regions
 * @see app/Http/Controllers/ImportController.php:28
 * @route '/imports/regions'
 */
regions.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return regions.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ImportController::regions
 * @see app/Http/Controllers/ImportController.php:28
 * @route '/imports/regions'
 */
regions.post = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: regions.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ImportController::provinces
 * @see app/Http/Controllers/ImportController.php:39
 * @route '/imports/provinces'
 */
export const provinces = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: provinces.url(options),
    method: 'post',
})

provinces.definition = {
    methods: ['post'],
    url: '/imports/provinces',
}

/**
* @see \App\Http\Controllers\ImportController::provinces
 * @see app/Http/Controllers/ImportController.php:39
 * @route '/imports/provinces'
 */
provinces.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return provinces.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ImportController::provinces
 * @see app/Http/Controllers/ImportController.php:39
 * @route '/imports/provinces'
 */
provinces.post = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: provinces.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\ImportController::cities
 * @see app/Http/Controllers/ImportController.php:50
 * @route '/imports/cities'
 */
export const cities = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: cities.url(options),
    method: 'post',
})

cities.definition = {
    methods: ['post'],
    url: '/imports/cities',
}

/**
* @see \App\Http\Controllers\ImportController::cities
 * @see app/Http/Controllers/ImportController.php:50
 * @route '/imports/cities'
 */
cities.url = (options?: { query?: QueryParams, mergeQuery?: QueryParams }) => {
    return cities.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\ImportController::cities
 * @see app/Http/Controllers/ImportController.php:50
 * @route '/imports/cities'
 */
cities.post = (options?: { query?: QueryParams, mergeQuery?: QueryParams }): {
    url: string,
    method: 'post',
} => ({
    url: cities.url(options),
    method: 'post',
})
const imports = {
    index,
regions,
provinces,
cities,
}

export default imports