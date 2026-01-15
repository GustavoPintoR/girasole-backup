<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta content="IE=edge,chrome=1" http-equiv="X-UA-Compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Humusense API Documentation</title>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.style.css") }}" media="screen">
    <link rel="stylesheet" href="{{ asset("/vendor/scribe/css/theme-default.print.css") }}" media="print">

    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.10/lodash.min.js"></script>

    <link rel="stylesheet"
          href="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/styles/obsidian.min.css">
    <script src="https://unpkg.com/@highlightjs/cdn-assets@11.6.0/highlight.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jets/0.14.1/jets.min.js"></script>

    <style id="language-style">
        /* starts out as display none and is replaced with js later  */
                    body .content .bash-example code { display: none; }
                    body .content .javascript-example code { display: none; }
                    body .content .python-example code { display: none; }
                    body .content .php-example code { display: none; }
            </style>

    <script>
        var tryItOutBaseUrl = "http://girasole.test";
        var useCsrf = Boolean();
        var csrfUrl = "/sanctum/csrf-cookie";
    </script>
    <script src="{{ asset("/vendor/scribe/js/tryitout-5.3.0.js") }}"></script>

    <script src="{{ asset("/vendor/scribe/js/theme-default-5.3.0.js") }}"></script>

</head>

<body data-languages="[&quot;bash&quot;,&quot;javascript&quot;,&quot;python&quot;,&quot;php&quot;]">

<a href="#" id="nav-button">
    <span>
        MENU
        <img src="{{ asset("/vendor/scribe/images/navbar.png") }}" alt="navbar-image"/>
    </span>
</a>
<div class="tocify-wrapper">
    
            <div class="lang-selector">
                                            <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                            <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                            <button type="button" class="lang-button" data-language-name="python">python</button>
                                            <button type="button" class="lang-button" data-language-name="php">php</button>
                    </div>
    
    <div class="search">
        <input type="text" class="search" id="input-search" placeholder="Search">
    </div>

    <div id="toc">
                    <ul id="tocify-header-introduction" class="tocify-header">
                <li class="tocify-item level-1" data-unique="introduction">
                    <a href="#introduction">Introduction</a>
                </li>
                            </ul>
                    <ul id="tocify-header-authenticating-requests" class="tocify-header">
                <li class="tocify-item level-1" data-unique="authenticating-requests">
                    <a href="#authenticating-requests">Authenticating requests</a>
                </li>
                            </ul>
                    <ul id="tocify-header-cities" class="tocify-header">
                <li class="tocify-item level-1" data-unique="cities">
                    <a href="#cities">Cities</a>
                </li>
                                    <ul id="tocify-subheader-cities" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="cities-GETapi-cities">
                                <a href="#cities-GETapi-cities">Get all cities.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="cities-GETapi-cities--id-">
                                <a href="#cities-GETapi-cities--id-">Get a specific city.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-events" class="tocify-header">
                <li class="tocify-item level-1" data-unique="events">
                    <a href="#events">Events</a>
                </li>
                                    <ul id="tocify-subheader-events" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="events-GETapi-calendar">
                                <a href="#events-GETapi-calendar">Get events for the current month where the user is an attendee.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-fields" class="tocify-header">
                <li class="tocify-item level-1" data-unique="fields">
                    <a href="#fields">Fields</a>
                </li>
                                    <ul id="tocify-subheader-fields" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="fields-GETapi-fields">
                                <a href="#fields-GETapi-fields">Get all fields for the authenticated user.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="fields-GETapi-fields--id-">
                                <a href="#fields-GETapi-fields--id-">Get a specific field.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="fields-GETapi-all-fields">
                                <a href="#fields-GETapi-all-fields">Get all fields for all users.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="fields-GETapi-fields--field--forecast-logs">
                                <a href="#fields-GETapi-fields--field--forecast-logs">Get the forecast logs for a field.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-plans" class="tocify-header">
                <li class="tocify-item level-1" data-unique="plans">
                    <a href="#plans">Plans</a>
                </li>
                                    <ul id="tocify-subheader-plans" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="plans-GETapi-plans">
                                <a href="#plans-GETapi-plans">Get all active plans.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="plans-POSTapi-subscribe--plan_id-">
                                <a href="#plans-POSTapi-subscribe--plan_id-">Generate a Stripe checkout URL for subscribing to a plan.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-postal-codes" class="tocify-header">
                <li class="tocify-item level-1" data-unique="postal-codes">
                    <a href="#postal-codes">Postal Codes</a>
                </li>
                                    <ul id="tocify-subheader-postal-codes" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="postal-codes-GETapi-postal-codes">
                                <a href="#postal-codes-GETapi-postal-codes">Get all postal codes.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="postal-codes-GETapi-postal-codes--id-">
                                <a href="#postal-codes-GETapi-postal-codes--id-">Get a specific postal code.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-provinces" class="tocify-header">
                <li class="tocify-item level-1" data-unique="provinces">
                    <a href="#provinces">Provinces</a>
                </li>
                                    <ul id="tocify-subheader-provinces" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="provinces-GETapi-provinces">
                                <a href="#provinces-GETapi-provinces">Get all provinces.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="provinces-GETapi-provinces--id-">
                                <a href="#provinces-GETapi-provinces--id-">Get a specific province.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-regions" class="tocify-header">
                <li class="tocify-item level-1" data-unique="regions">
                    <a href="#regions">Regions</a>
                </li>
                                    <ul id="tocify-subheader-regions" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="regions-GETapi-regions">
                                <a href="#regions-GETapi-regions">Get all regions.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="regions-GETapi-regions--id-">
                                <a href="#regions-GETapi-regions--id-">Get a specific region.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-sensors" class="tocify-header">
                <li class="tocify-item level-1" data-unique="sensors">
                    <a href="#sensors">Sensors</a>
                </li>
                                    <ul id="tocify-subheader-sensors" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="sensors-GETapi-sensors">
                                <a href="#sensors-GETapi-sensors">Display a paginated list of sensors with their type, operations, and cadastral group.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="sensors-GETapi-sensors--id-">
                                <a href="#sensors-GETapi-sensors--id-">Display a sensor with its type, operations, and cadastral group.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="sensors-GETapi-sensor-influx">
                                <a href="#sensors-GETapi-sensor-influx">Get a sensor by ID with data from InfluxDB.</a>
                            </li>
                                                                        </ul>
                            </ul>
                    <ul id="tocify-header-user-management" class="tocify-header">
                <li class="tocify-item level-1" data-unique="user-management">
                    <a href="#user-management">User management</a>
                </li>
                                    <ul id="tocify-subheader-user-management" class="tocify-subheader">
                                                    <li class="tocify-item level-2" data-unique="user-management-POSTapi-login">
                                <a href="#user-management-POSTapi-login">Authenticate a user</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-management-POSTapi-register">
                                <a href="#user-management-POSTapi-register">Register a new user.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-management-POSTapi-password-reset">
                                <a href="#user-management-POSTapi-password-reset">Send a password reset link to the user's email address.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-management-GETapi-me">
                                <a href="#user-management-GETapi-me">Retrieve the authenticated user's profile and associated data.</a>
                            </li>
                                                                                <li class="tocify-item level-2" data-unique="user-management-PATCHapi-update-profile">
                                <a href="#user-management-PATCHapi-update-profile">Update the authenticated user's profile, billing info, billing address, and/or shipping address.</a>
                            </li>
                                                                        </ul>
                            </ul>
            </div>

    <ul class="toc-footer" id="toc-footer">
                    <li style="padding-bottom: 5px;"><a href="{{ route("scribe.postman") }}">View Postman collection</a></li>
                            <li style="padding-bottom: 5px;"><a href="{{ route("scribe.openapi") }}">View OpenAPI spec</a></li>
                <li><a href="http://github.com/knuckleswtf/scribe">Documentation powered by Scribe ✍</a></li>
    </ul>

    <ul class="toc-footer" id="last-updated">
        <li>Last updated: December 6, 2025</li>
    </ul>
</div>

<div class="page-wrapper">
    <div class="dark-box"></div>
    <div class="content">
        <h1 id="introduction">Introduction</h1>
<p>Girasole's External API Documentation</p>
<aside>
    <strong>Base URL</strong>: <code>http://girasole.test</code>
</aside>
<p>This documentation aims to provide all the information you need to work with our API.</p>
<h3 id="key-concepts">Key Concepts</h3>
<ul>
<li>
<p><strong>Content-Type Header</strong>: Always use <code>application/json</code> when sending data to the API.</p>
</li>
<li>
<p><strong>Accept Header</strong>: Set to <code>application/json</code> to receive JSON responses.</p>
</li>
<li>
<p><strong>App Identification (Required on every request)</strong>:
Every single request: even public ones must include the header:
<code>X-App-Authentication: YOUR_APP_TOKEN</code>
This token identifies <strong>which application</strong> is calling the API (mobile app, docs, partner system, etc.).
<strong>Important</strong>: This header must contain the <strong>raw app token</strong> (e.g. <code>17|aKMj6PXo...</code>).
The <code>Bearer</code> prefix is <strong>not allowed</strong> here and will be rejected.</p>
</li>
<li>
<p><strong>User Authentication (Protected routes only)</strong>
For endpoints that require a logged-in user, include the standard Sanctum token:
<code>Authorization: Bearer YOUR_USER_TOKEN</code>
This is completely separate from the app token above.</p>
</li>
</ul>
<aside>
All requests must include a valid <code>X-App-Authentication</code> header with an approved app token.
User tokens sent in this header will be rejected. Bearer prefix in this header is forbidden.
</aside>
<aside>As you scroll, you'll see code examples for working with the API in different programming languages in the dark area to the right (or as part of the content on mobile).</aside>

        <h1 id="authenticating-requests">Authenticating requests</h1>
<p>To authenticate requests, include an <strong><code>Authorization</code></strong> header with the value <strong><code>"Bearer {ACCESS_TOKEN}"</code></strong>.</p>
<p>All authenticated endpoints are marked with a <code>requires authentication</code> badge in the documentation below.</p>
<ul>
<li>A Bearer token is like a secure key to access protected parts of the API.</li>
<li>Include it in the <code>Authorization</code> header of every request, like: <code>Authorization: Bearer YOUR_TOKEN_HERE</code>.</li>
<li>If you forget the token or use an invalid one, you'll get a 401 Unauthorized error.</li>
<li>Always keep your token secret – don't share it!</li>
</ul>
<p>For security, tokens may expire; check our [auth endpoint or docs] for refresh instructions.</p>

        <h1 id="cities">Cities</h1>

    <p>API for cities</p>

                                <h2 id="cities-GETapi-cities">Get all cities.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns a list of cities, including province, region, and postal codes.</p>

<span id="example-requests-GETapi-cities">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/cities" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/cities"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/cities'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/cities';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-cities">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Cities retrieved successfully&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Milan&quot;,
            &quot;cadastral_code&quot;: &quot;MIL&quot;,
            &quot;province_id&quot;: 1,
            &quot;region_id&quot;: 1,
            &quot;province&quot;: {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Milan&quot;,
                &quot;code&quot;: &quot;MI&quot;
            },
            &quot;region&quot;: {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Lombardy&quot;,
                &quot;code&quot;: &quot;IT-LOM&quot;
            },
            &quot;postalCodes&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;code&quot;: &quot;20121&quot;,
                    &quot;pivot&quot;: {
                        &quot;zone&quot;: &quot;Central&quot;,
                        &quot;notes&quot;: &quot;Downtown area&quot;
                    }
                }
            ]
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-cities" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-cities"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-cities"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-cities" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-cities">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-cities" data-method="GET"
      data-path="api/cities"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-cities', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-cities"
                    onclick="tryItOut('GETapi-cities');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-cities"
                    onclick="cancelTryOut('GETapi-cities');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-cities"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/cities</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-cities"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-cities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-cities"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-cities"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                    <h2 id="cities-GETapi-cities--id-">Get a specific city.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns details of a city, optionally including province, region, and postal codes.</p>

<span id="example-requests-GETapi-cities--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/cities/1?include=province%2Cregion%2CpostalCodes" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"id\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/cities/1"
);

const params = {
    "include": "province,region,postalCodes",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "id": 1
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/cities/1'
payload = {
    "id": 1
}
params = {
  'include': 'province,region,postalCodes',
}
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers, json=payload, params=params)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/cities/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'query' =&gt; [
            'include' =&gt; 'province,region,postalCodes',
        ],
        'json' =&gt; [
            'id' =&gt; 1,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-cities--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;City retrieved successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Milan&quot;,
        &quot;cadastral_code&quot;: &quot;MIL&quot;,
        &quot;province_id&quot;: 1,
        &quot;region_id&quot;: 1,
        &quot;province&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Milan&quot;,
            &quot;code&quot;: &quot;MI&quot;
        },
        &quot;region&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Lombardy&quot;,
            &quot;code&quot;: &quot;IT-LOM&quot;
        },
        &quot;postalCodes&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;code&quot;: &quot;20121&quot;,
                &quot;pivot&quot;: {
                    &quot;zone&quot;: &quot;Central&quot;,
                    &quot;notes&quot;: &quot;Downtown area&quot;
                }
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;City not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;id&quot;: [
            &quot;The id field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-cities--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-cities--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-cities--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-cities--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-cities--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-cities--id-" data-method="GET"
      data-path="api/cities/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-cities--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-cities--id-"
                    onclick="tryItOut('GETapi-cities--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-cities--id-"
                    onclick="cancelTryOut('GETapi-cities--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-cities--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/cities/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-cities--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-cities--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-cities--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-cities--id-"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-cities--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the city. Example: <code>1</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>include</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="include"                data-endpoint="GETapi-cities--id-"
               value="province,region,postalCodes"
               data-component="query">
    <br>
<p>Comma-separated list of relationships to include (province, region, postalCodes). Example: <code>province,region,postalCodes</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-cities--id-"
               value="1"
               data-component="body">
    <br>
<p>The ID of the city. Example: <code>1</code></p>
        </div>
        </form>

                <h1 id="events">Events</h1>

    <p>API for events (read-only)</p>

                                <h2 id="events-GETapi-calendar">Get events for the current month where the user is an attendee.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns a list of events for the authenticated user, filtered to the current month.</p>

<span id="example-requests-GETapi-calendar">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/calendar" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/calendar"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/calendar'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/calendar';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-calendar">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Events retrieved successfully&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;start&quot;: &quot;2025-10-05&quot;,
            &quot;end&quot;: &quot;2025-10-05&quot;,
            &quot;title&quot;: &quot;Team Meeting&quot;,
            &quot;description&quot;: &quot;Weekly sync&quot;
        },
        {
            &quot;id&quot;: 2,
            &quot;start&quot;: &quot;2025-10-10 14:00&quot;,
            &quot;end&quot;: &quot;2025-10-10 15:00&quot;,
            &quot;title&quot;: &quot;Client Call&quot;,
            &quot;description&quot;: &quot;Discuss project&quot;
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-calendar" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-calendar"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-calendar"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-calendar" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-calendar">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-calendar" data-method="GET"
      data-path="api/calendar"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-calendar', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-calendar"
                    onclick="tryItOut('GETapi-calendar');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-calendar"
                    onclick="cancelTryOut('GETapi-calendar');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-calendar"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/calendar</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-calendar"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-calendar"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-calendar"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-calendar"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                <h1 id="fields">Fields</h1>

    <p>API for fields (read-only)</p>

                                <h2 id="fields-GETapi-fields">Get all fields for the authenticated user.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns a list of fields with their info, GeoJSON, and sensors.</p>

<span id="example-requests-GETapi-fields">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/fields" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/fields"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/fields'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/fields';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-fields">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Fields retrieved successfully&quot;,
  &quot;data&quot;: [
    {
      &quot;id&quot;: 1,
      &quot;user_id&quot;: 1,
      &quot;name&quot;: &quot;Field A&quot;,
      &quot;description&quot;: &quot;Main field&quot;,
      &quot;total_area&quot;: 5000.5,
      &quot;units_count&quot;: 2,
      &quot;color&quot;: &quot;#FF0000&quot;,
      &quot;creation_method&quot;: &quot;units&quot;,
      &quot;original_geojson&quot;: {...},
      &quot;boundary_geometry_json&quot;: {&quot;type&quot;: &quot;MultiPolygon&quot;, &quot;coordinates&quot;: [...]},
      &quot;centroid_json&quot;: {&quot;type&quot;: &quot;Point&quot;, &quot;coordinates&quot;: [10.0, 20.0]},
      &quot;sensors&quot;: [
        {
          &quot;id&quot;: 1,
          &quot;name&quot;: &quot;Sensor 1&quot;,
          &quot;type&quot;: &quot;temperature&quot;,
          &quot;status&quot;: &quot;active&quot;
        }
      ]
    }
  ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-fields" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-fields"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-fields"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-fields" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-fields">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-fields" data-method="GET"
      data-path="api/fields"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-fields', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-fields"
                    onclick="tryItOut('GETapi-fields');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-fields"
                    onclick="cancelTryOut('GETapi-fields');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-fields"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/fields</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-fields"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-fields"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-fields"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-fields"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                    <h2 id="fields-GETapi-fields--id-">Get a specific field.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns details of a field, including GeoJSON and sensors, if the user owns it.</p>

<span id="example-requests-GETapi-fields--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/fields/architecto" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/fields/architecto"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/fields/architecto'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/fields/architecto';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-fields--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Field retrieved successfully&quot;,
  &quot;data&quot;: {
    &quot;id&quot;: 1,
    &quot;user_id&quot;: 1,
    &quot;name&quot;: &quot;Field A&quot;,
    &quot;description&quot;: &quot;Main field&quot;,
    &quot;total_area&quot;: 5000.5,
    &quot;units_count&quot;: 2,
    &quot;color&quot;: &quot;#FF0000&quot;,
    &quot;creation_method&quot;: &quot;units&quot;,
    &quot;original_geojson&quot;: {...},
    &quot;boundary_geometry_json&quot;: {&quot;type&quot;: &quot;MultiPolygon&quot;, &quot;coordinates&quot;: [...]},
    &quot;centroid_json&quot;: {&quot;type&quot;: &quot;Point&quot;, &quot;coordinates&quot;: [10.0, 20.0]},
    &quot;sensors&quot;: [
      {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Sensor 1&quot;,
        &quot;type&quot;: &quot;temperature&quot;,
        &quot;status&quot;: &quot;active&quot;
      }
    ],
    &quot;recent_forecast_logs&quot;: [
       {
         &quot;id&quot;: 189,
         &quot;status&quot;: &quot;success&quot;,
         &quot;ran_at&quot;: &quot;2025-11-26T14:32:10Z&quot;,
         &quot;parameters&quot;: { &quot;horizon&quot;: 14, &quot;model&quot;: &quot;v3&quot; },
         &quot;data&quot;: {
           &quot;temperature&quot;: [ ... ],
           &quot;precipitation&quot;: [ ... ]
         },
         &quot;created_at&quot;: &quot;2025-11-26T14:32:15Z&quot;
       },
       {
         &quot;id&quot;: 178,
         &quot;status&quot;: &quot;success&quot;,
         &quot;ran_at&quot;: &quot;2025-11-25T08:11:05Z&quot;,
         &quot;parameters&quot;: { &quot;horizon&quot;: 14 },
         &quot;data&quot;: { ... },
         &quot;created_at&quot;: &quot;2025-11-25T08:11:10Z&quot;
       }
     ]
  }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthorized to view this field&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Field not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;id&quot;: [
            &quot;The id field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-fields--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-fields--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-fields--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-fields--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-fields--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-fields--id-" data-method="GET"
      data-path="api/fields/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-fields--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-fields--id-"
                    onclick="tryItOut('GETapi-fields--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-fields--id-"
                    onclick="cancelTryOut('GETapi-fields--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-fields--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/fields/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-fields--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-fields--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-fields--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-fields--id-"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="id"                data-endpoint="GETapi-fields--id-"
               value="architecto"
               data-component="url">
    <br>
<p>The ID of the field. Example: <code>architecto</code></p>
            </div>
                    </form>

                    <h2 id="fields-GETapi-all-fields">Get all fields for all users.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>This endpoint only works if you have an integration role/token.
Returns a list of fields in the system with their info, GeoJSON, and sensors.</p>

<span id="example-requests-GETapi-all-fields">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/all/fields" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/all/fields"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/all/fields'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/all/fields';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-all-fields">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Fields retrieved successfully&quot;,
  &quot;data&quot;: [
    {
      &quot;id&quot;: 1,
      &quot;user_id&quot;: 1,
      &quot;name&quot;: &quot;Field A&quot;,
      &quot;description&quot;: &quot;Main field&quot;,
      &quot;total_area&quot;: 5000.5,
      &quot;units_count&quot;: 2,
      &quot;color&quot;: &quot;#FF0000&quot;,
      &quot;creation_method&quot;: &quot;units&quot;,
      &quot;original_geojson&quot;: {...},
      &quot;boundary_geometry_json&quot;: {&quot;type&quot;: &quot;MultiPolygon&quot;, &quot;coordinates&quot;: [...]},
      &quot;centroid_json&quot;: {&quot;type&quot;: &quot;Point&quot;, &quot;coordinates&quot;: [10.0, 20.0]},
      &quot;sensors&quot;: [
        {
          &quot;id&quot;: 1,
          &quot;name&quot;: &quot;Sensor 1&quot;,
          &quot;type&quot;: &quot;temperature&quot;,
          &quot;status&quot;: &quot;active&quot;
        }
      ]
    }
  ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Oops, seems you are not authorized to access this api.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-all-fields" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-all-fields"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-all-fields"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-all-fields" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-all-fields">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-all-fields" data-method="GET"
      data-path="api/all/fields"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-all-fields', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-all-fields"
                    onclick="tryItOut('GETapi-all-fields');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-all-fields"
                    onclick="cancelTryOut('GETapi-all-fields');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-all-fields"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/all/fields</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-all-fields"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-all-fields"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-all-fields"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-all-fields"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                    <h2 id="fields-GETapi-fields--field--forecast-logs">Get the forecast logs for a field.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>



<span id="example-requests-GETapi-fields--field--forecast-logs">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/fields/16/forecast-logs" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/fields/16/forecast-logs"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/fields/16/forecast-logs'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/fields/16/forecast-logs';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-fields--field--forecast-logs">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Forecast logs retrieved successfully&quot;,
  &quot;data&quot;: {
    &quot;forecast_logs&quot;: [
     {
          &quot;id&quot;: 189,
          &quot;status&quot;: &quot;success&quot;,
          &quot;ran_at&quot;: &quot;2025-11-26T14:32:10Z&quot;,
          &quot;parameters&quot;: { &quot;horizon&quot;: 14, &quot;model&quot;: &quot;v3&quot; },
          &quot;data&quot;: {
            &quot;temperature&quot;: [ ... ],
            &quot;precipitation&quot;: [ ... ]
          },
          &quot;created_at&quot;: &quot;2025-11-26T14:32:15Z&quot;
      },
    ]
  }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthorized to view this field&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Field not found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-fields--field--forecast-logs" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-fields--field--forecast-logs"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-fields--field--forecast-logs"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-fields--field--forecast-logs" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-fields--field--forecast-logs">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-fields--field--forecast-logs" data-method="GET"
      data-path="api/fields/{field}/forecast-logs"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-fields--field--forecast-logs', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-fields--field--forecast-logs"
                    onclick="tryItOut('GETapi-fields--field--forecast-logs');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-fields--field--forecast-logs"
                    onclick="cancelTryOut('GETapi-fields--field--forecast-logs');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-fields--field--forecast-logs"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/fields/{field}/forecast-logs</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-fields--field--forecast-logs"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-fields--field--forecast-logs"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-fields--field--forecast-logs"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-fields--field--forecast-logs"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>field</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="field"                data-endpoint="GETapi-fields--field--forecast-logs"
               value="16"
               data-component="url">
    <br>
<p>The ID of the field Example: <code>16</code></p>
            </div>
                    </form>

                <h1 id="plans">Plans</h1>

    <p>API for users to subscribe to a plan</p>

                                <h2 id="plans-GETapi-plans">Get all active plans.</h2>

<p>
</p>

<p>Returns a list of all active subscription plans available.</p>

<span id="example-requests-GETapi-plans">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/plans" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/plans"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/plans'
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/plans';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-plans">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Plans retrieved successfully&quot;,
    &quot;plans&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Basic Plan&quot;,
            &quot;stripe_price_id&quot;: &quot;price_123&quot;,
            &quot;features&quot;: [
                &quot;feature1&quot;,
                &quot;feature2&quot;
            ],
            &quot;active&quot;: true
        },
        {
            &quot;id&quot;: 2,
            &quot;name&quot;: &quot;Pro Plan&quot;,
            &quot;stripe_price_id&quot;: &quot;price_456&quot;,
            &quot;features&quot;: [
                &quot;feature1&quot;,
                &quot;feature2&quot;,
                &quot;feature3&quot;
            ],
            &quot;active&quot;: true
        }
    ]
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-plans" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-plans"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-plans"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-plans" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-plans">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-plans" data-method="GET"
      data-path="api/plans"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-plans', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-plans"
                    onclick="tryItOut('GETapi-plans');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-plans"
                    onclick="cancelTryOut('GETapi-plans');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-plans"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/plans</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-plans"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-plans"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-plans"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                    <h2 id="plans-POSTapi-subscribe--plan_id-">Generate a Stripe checkout URL for subscribing to a plan.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Creates a Stripe checkout session for the specified plan and returns the URL.</p>

<span id="example-requests-POSTapi-subscribe--plan_id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://girasole.test/api/subscribe/1" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"plan_id\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/subscribe/1"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "plan_id": 1
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/subscribe/1'
payload = {
    "plan_id": 1
}
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/subscribe/1';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'json' =&gt; [
            'plan_id' =&gt; 1,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-subscribe--plan_id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Checkout URL generated successfully&quot;,
    &quot;checkout_url&quot;: &quot;https://checkout.stripe.com/pay/cs_123&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;The token has read permission only&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Plan not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (409):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;User is already subscribed&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Plan is missing Stripe price ID&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-subscribe--plan_id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-subscribe--plan_id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-subscribe--plan_id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-subscribe--plan_id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-subscribe--plan_id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-subscribe--plan_id-" data-method="POST"
      data-path="api/subscribe/{plan_id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-subscribe--plan_id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-subscribe--plan_id-"
                    onclick="tryItOut('POSTapi-subscribe--plan_id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-subscribe--plan_id-"
                    onclick="cancelTryOut('POSTapi-subscribe--plan_id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-subscribe--plan_id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/subscribe/{plan_id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="POSTapi-subscribe--plan_id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-subscribe--plan_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-subscribe--plan_id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="POSTapi-subscribe--plan_id-"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>plan_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="plan_id"                data-endpoint="POSTapi-subscribe--plan_id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the plan. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>plan_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="plan_id"                data-endpoint="POSTapi-subscribe--plan_id-"
               value="1"
               data-component="body">
    <br>
<p>The ID of the plan to subscribe to. Example: <code>1</code></p>
        </div>
        </form>

                <h1 id="postal-codes">Postal Codes</h1>

    <p>API for postal codes</p>

                                <h2 id="postal-codes-GETapi-postal-codes">Get all postal codes.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns a list of postal codes, including cities.</p>

<span id="example-requests-GETapi-postal-codes">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/postal-codes" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/postal-codes"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/postal-codes'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/postal-codes';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-postal-codes">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Postal codes retrieved successfully&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;code&quot;: &quot;20121&quot;,
            &quot;cities&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Milan&quot;,
                    &quot;cadastral_code&quot;: &quot;MIL&quot;,
                    &quot;pivot&quot;: {
                        &quot;zone&quot;: &quot;Central&quot;,
                        &quot;notes&quot;: &quot;Downtown area&quot;
                    }
                }
            ]
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-postal-codes" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-postal-codes"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-postal-codes"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-postal-codes" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-postal-codes">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-postal-codes" data-method="GET"
      data-path="api/postal-codes"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-postal-codes', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-postal-codes"
                    onclick="tryItOut('GETapi-postal-codes');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-postal-codes"
                    onclick="cancelTryOut('GETapi-postal-codes');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-postal-codes"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/postal-codes</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-postal-codes"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-postal-codes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-postal-codes"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-postal-codes"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                    <h2 id="postal-codes-GETapi-postal-codes--id-">Get a specific postal code.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns details of a postal code, including cities.</p>

<span id="example-requests-GETapi-postal-codes--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/postal-codes/1" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"id\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/postal-codes/1"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "id": 1
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/postal-codes/1'
payload = {
    "id": 1
}
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/postal-codes/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'json' =&gt; [
            'id' =&gt; 1,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-postal-codes--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Postal code retrieved successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;code&quot;: &quot;20121&quot;,
        &quot;cities&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Milan&quot;,
                &quot;cadastral_code&quot;: &quot;MIL&quot;,
                &quot;pivot&quot;: {
                    &quot;zone&quot;: &quot;Central&quot;,
                    &quot;notes&quot;: &quot;Downtown area&quot;
                }
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Postal code not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;id&quot;: [
            &quot;The id field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-postal-codes--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-postal-codes--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-postal-codes--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-postal-codes--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-postal-codes--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-postal-codes--id-" data-method="GET"
      data-path="api/postal-codes/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-postal-codes--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-postal-codes--id-"
                    onclick="tryItOut('GETapi-postal-codes--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-postal-codes--id-"
                    onclick="cancelTryOut('GETapi-postal-codes--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-postal-codes--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/postal-codes/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-postal-codes--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-postal-codes--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-postal-codes--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-postal-codes--id-"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-postal-codes--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the postal code. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-postal-codes--id-"
               value="1"
               data-component="body">
    <br>
<p>The ID of the postal code. Example: <code>1</code></p>
        </div>
        </form>

                <h1 id="provinces">Provinces</h1>

    <p>API for provinces</p>

                                <h2 id="provinces-GETapi-provinces">Get all provinces.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns a list of provinces, including region and cities.</p>

<span id="example-requests-GETapi-provinces">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/provinces" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/provinces"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/provinces'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/provinces';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-provinces">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Provinces retrieved successfully&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Milan&quot;,
            &quot;code&quot;: &quot;MI&quot;,
            &quot;region_id&quot;: 1,
            &quot;region&quot;: {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Lombardy&quot;,
                &quot;code&quot;: &quot;IT-LOM&quot;
            },
            &quot;cities&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Milan&quot;,
                    &quot;cadastral_code&quot;: &quot;MIL&quot;
                }
            ]
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-provinces" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-provinces"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-provinces"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-provinces" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-provinces">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-provinces" data-method="GET"
      data-path="api/provinces"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-provinces', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-provinces"
                    onclick="tryItOut('GETapi-provinces');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-provinces"
                    onclick="cancelTryOut('GETapi-provinces');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-provinces"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/provinces</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-provinces"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-provinces"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-provinces"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-provinces"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                    <h2 id="provinces-GETapi-provinces--id-">Get a specific province.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns details of a province, including region and cities.</p>

<span id="example-requests-GETapi-provinces--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/provinces/1" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"id\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/provinces/1"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "id": 1
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/provinces/1'
payload = {
    "id": 1
}
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/provinces/1';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'json' =&gt; [
            'id' =&gt; 1,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-provinces--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Province retrieved successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Milan&quot;,
        &quot;code&quot;: &quot;MI&quot;,
        &quot;region_id&quot;: 1,
        &quot;region&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Lombardy&quot;,
            &quot;code&quot;: &quot;IT-LOM&quot;
        },
        &quot;cities&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Milan&quot;,
                &quot;cadastral_code&quot;: &quot;MIL&quot;
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Province not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;id&quot;: [
            &quot;The id field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-provinces--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-provinces--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-provinces--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-provinces--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-provinces--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-provinces--id-" data-method="GET"
      data-path="api/provinces/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-provinces--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-provinces--id-"
                    onclick="tryItOut('GETapi-provinces--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-provinces--id-"
                    onclick="cancelTryOut('GETapi-provinces--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-provinces--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/provinces/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-provinces--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-provinces--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-provinces--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-provinces--id-"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-provinces--id-"
               value="1"
               data-component="url">
    <br>
<p>The ID of the province. Example: <code>1</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-provinces--id-"
               value="1"
               data-component="body">
    <br>
<p>The ID of the province. Example: <code>1</code></p>
        </div>
        </form>

                <h1 id="regions">Regions</h1>

    <p>API for regions</p>

                                <h2 id="regions-GETapi-regions">Get all regions.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns a list of regions, including provinces and cities.</p>

<span id="example-requests-GETapi-regions">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/regions" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/regions"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/regions'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/regions';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-regions">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Regions retrieved successfully&quot;,
    &quot;data&quot;: [
        {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Lombardy&quot;,
            &quot;code&quot;: &quot;IT-LOM&quot;,
            &quot;provinces&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Milan&quot;,
                    &quot;code&quot;: &quot;MI&quot;
                }
            ],
            &quot;cities&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Milan&quot;,
                    &quot;code&quot;: &quot;MIL&quot;
                }
            ]
        }
    ]
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-regions" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-regions"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-regions"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-regions" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-regions">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-regions" data-method="GET"
      data-path="api/regions"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-regions', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-regions"
                    onclick="tryItOut('GETapi-regions');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-regions"
                    onclick="cancelTryOut('GETapi-regions');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-regions"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/regions</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-regions"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-regions"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-regions"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-regions"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                    <h2 id="regions-GETapi-regions--id-">Get a specific region.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns details of a region, including provinces and cities.</p>

<span id="example-requests-GETapi-regions--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/regions/11" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"id\": 1
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/regions/11"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "id": 1
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/regions/11'
payload = {
    "id": 1
}
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/regions/11';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'json' =&gt; [
            'id' =&gt; 1,
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-regions--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Region retrieved successfully&quot;,
    &quot;data&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Lombardy&quot;,
        &quot;code&quot;: &quot;IT-LOM&quot;,
        &quot;provinces&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Milan&quot;,
                &quot;code&quot;: &quot;MI&quot;
            }
        ],
        &quot;cities&quot;: [
            {
                &quot;id&quot;: 1,
                &quot;name&quot;: &quot;Milan&quot;,
                &quot;code&quot;: &quot;MIL&quot;
            }
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Payment required&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Region not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;id&quot;: [
            &quot;The id field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-regions--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-regions--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-regions--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-regions--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-regions--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-regions--id-" data-method="GET"
      data-path="api/regions/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-regions--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-regions--id-"
                    onclick="tryItOut('GETapi-regions--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-regions--id-"
                    onclick="cancelTryOut('GETapi-regions--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-regions--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/regions/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-regions--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-regions--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-regions--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-regions--id-"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-regions--id-"
               value="11"
               data-component="url">
    <br>
<p>The ID of the region. Example: <code>11</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-regions--id-"
               value="1"
               data-component="body">
    <br>
<p>The ID of the region. Example: <code>1</code></p>
        </div>
        </form>

                <h1 id="sensors">Sensors</h1>

    <p>API for sensor</p>

                                <h2 id="sensors-GETapi-sensors">Display a paginated list of sensors with their type, operations, and cadastral group.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>This endpoint retrieves a list of sensors, optionally filtered by type, for the authenticated user
or users with read-only permissions. Includes sensor type, operations, cadastral group, and owner details.</p>

<span id="example-requests-GETapi-sensors">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/sensors?type=temperature&amp;page=1&amp;per_page=10" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/sensors"
);

const params = {
    "type": "temperature",
    "page": "1",
    "per_page": "10",
};
Object.keys(params)
    .forEach(key =&gt; url.searchParams.append(key, params[key]));

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/sensors'
params = {
  'type': 'temperature',
  'page': '1',
  'per_page': '10',
}
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers, params=params)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/sensors';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'query' =&gt; [
            'type' =&gt; 'temperature',
            'page' =&gt; '1',
            'per_page' =&gt; '10',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-sensors">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Sensors retrieved successfully&quot;,
  &quot;data&quot;: [
      {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Temperature Sensor&quot;,
        &quot;type&quot;: &quot;temperature&quot;,
        &quot;serial_number&quot;: &quot;SN123456&quot;,
        &quot;urn&quot;: &quot;urn:sensor:123&quot;,
        &quot;iccid&quot;: &quot;1234567890123456789&quot;,
        &quot;transmission_module_identification&quot;: &quot;TM123&quot;,
        &quot;description&quot;: &quot;A temperature sensor for environmental monitoring&quot;,
        &quot;latitude&quot;: &quot;40.71280000&quot;,
        &quot;longitude&quot;: &quot;-74.00600000&quot;,
        &quot;firmware&quot;: &quot;v1.0.0&quot;,
        &quot;metadata&quot;: {&quot;calibration&quot;: &quot;2025-01-01&quot;},
        &quot;sensor_type&quot;: {
          &quot;id&quot;: 1,
          &quot;name&quot;: &quot;Temperature&quot;,
          &quot;description&quot;: &quot;Measures ambient temperature&quot;,
          &quot;operations&quot;: [
            {
              &quot;id&quot;: 1,
              &quot;name&quot;: &quot;Read Temperature&quot;,
              &quot;description&quot;: &quot;Reads current temperature in Celsius&quot;
            }
          ]
        },
        &quot;fields&quot;: {
          &quot;id&quot;: 1,
          &quot;name&quot;: &quot;Downtown Monitoring&quot;
        },
        &quot;owner&quot;: {
          &quot;id&quot;: 1,
          &quot;name&quot;: &quot;John Doe&quot;,
          &quot;email&quot;: &quot;john@example.com&quot;
        },
        &quot;created_at&quot;: &quot;2025-10-03T09:33:00Z&quot;
      }
    ],
  }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthorized&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-sensors" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-sensors"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-sensors"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-sensors" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-sensors">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-sensors" data-method="GET"
      data-path="api/sensors"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-sensors', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-sensors"
                    onclick="tryItOut('GETapi-sensors');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-sensors"
                    onclick="cancelTryOut('GETapi-sensors');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-sensors"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/sensors</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-sensors"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-sensors"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-sensors"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-sensors"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                            <h4 class="fancy-heading-panel"><b>Query Parameters</b></h4>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="GETapi-sensors"
               value="temperature"
               data-component="query">
    <br>
<p>Optional. Filter sensors by type. Example: <code>temperature</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="page"                data-endpoint="GETapi-sensors"
               value="1"
               data-component="query">
    <br>
<p>Optional. Page number for pagination. Example: <code>1</code></p>
            </div>
                                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>per_page</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="per_page"                data-endpoint="GETapi-sensors"
               value="10"
               data-component="query">
    <br>
<p>Optional. Number of items per page (default: 10). Example: <code>10</code></p>
            </div>
                </form>

                    <h2 id="sensors-GETapi-sensors--id-">Display a sensor with its type, operations, and cadastral group.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>This endpoint retrieves details of a specific sensor, including its sensor type,
associated operations, and cadastral group. Only accessible with read-only permissions
or by the sensor's owner.</p>

<span id="example-requests-GETapi-sensors--id-">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/sensors/7" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/sensors/7"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/sensors/7'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/sensors/7';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-sensors--id-">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Sensor retrieved successfully&quot;,
    &quot;sensor&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Temperature Sensor&quot;,
        &quot;type&quot;: &quot;temperature&quot;,
        &quot;serial_number&quot;: &quot;SN123456&quot;,
        &quot;urn&quot;: &quot;urn:sensor:123&quot;,
        &quot;iccid&quot;: &quot;1234567890123456789&quot;,
        &quot;transmission_module_identification&quot;: &quot;TM123&quot;,
        &quot;description&quot;: &quot;A temperature sensor for environmental monitoring&quot;,
        &quot;latitude&quot;: &quot;40.71280000&quot;,
        &quot;longitude&quot;: &quot;-74.00600000&quot;,
        &quot;firmware&quot;: &quot;v1.0.0&quot;,
        &quot;metadata&quot;: {
            &quot;calibration&quot;: &quot;2025-01-01&quot;
        },
        &quot;sensor_type&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Temperature&quot;,
            &quot;description&quot;: &quot;Measures ambient temperature&quot;,
            &quot;operations&quot;: [
                {
                    &quot;id&quot;: 1,
                    &quot;name&quot;: &quot;Read Temperature&quot;,
                    &quot;description&quot;: &quot;Reads current temperature in Celsius&quot;
                }
            ]
        },
        &quot;fields&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;Downtown Monitoring&quot;
        },
        &quot;owner&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;John Doe&quot;,
            &quot;email&quot;: &quot;john@example.com&quot;
        },
        &quot;created_at&quot;: &quot;2025-10-03T09:33:00Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthorized&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Sensor not found&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-sensors--id-" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-sensors--id-"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-sensors--id-"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-sensors--id-" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-sensors--id-">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-sensors--id-" data-method="GET"
      data-path="api/sensors/{id}"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-sensors--id-', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-sensors--id-"
                    onclick="tryItOut('GETapi-sensors--id-');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-sensors--id-"
                    onclick="cancelTryOut('GETapi-sensors--id-');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-sensors--id-"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/sensors/{id}</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-sensors--id-"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-sensors--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-sensors--id-"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-sensors--id-"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        <h4 class="fancy-heading-panel"><b>URL Parameters</b></h4>
                    <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="id"                data-endpoint="GETapi-sensors--id-"
               value="7"
               data-component="url">
    <br>
<p>The ID of the sensor. Example: <code>7</code></p>
            </div>
                    </form>

                    <h2 id="sensors-GETapi-sensor-influx">Get a sensor by ID with data from InfluxDB.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>Returns sensor data with the most recent measurements for the specified field from influxdb.</p>

<span id="example-requests-GETapi-sensor-influx">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/sensor/influx" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --data "{
    \"sensor_id\": 1,
    \"time_range\": \"30\",
    \"sensor_operation\": \"T01\",
    \"field\": \"CH\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/sensor/influx"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
};

let body = {
    "sensor_id": 1,
    "time_range": "30",
    "sensor_operation": "T01",
    "field": "CH"
};

fetch(url, {
    method: "GET",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/sensor/influx'
payload = {
    "sensor_id": 1,
    "time_range": "30",
    "sensor_operation": "T01",
    "field": "CH"
}
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json'
}

response = requests.request('GET', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/sensor/influx';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
        ],
        'json' =&gt; [
            'sensor_id' =&gt; 1,
            'time_range' =&gt; '30',
            'sensor_operation' =&gt; 'T01',
            'field' =&gt; 'CH',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-sensor-influx">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Sensor retrieved successfully&quot;,
  &quot;data&quot;: {
    &quot;sensor&quot;: {
      &quot;id&quot;: 1,
      &quot;serial_number&quot;: &quot;123&quot;,
      &quot;urn:&quot;1004828F2&quot;,
      &quot;sensor_type&quot;: {
          &quot;name&quot;: &quot;Malisimo&quot;
      },
      &quot;fields&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;Field A&quot;
      },
      &quot;longitude&quot;: 10.0,
      &quot;latitude&quot;: 20.0,
      &quot;description&quot;: &quot;Main sensor&quot;,
      &quot;firmware&quot;: &quot;200&quot;
    },
     &quot;measurements&quot;: []
  }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthorized to view this sensor&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (404):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Sensor not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;sensor_id&quot;: [
            &quot;The sensor_id field is required.&quot;
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;URN not found&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Failed to fetch measurement&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-sensor-influx" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-sensor-influx"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-sensor-influx"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-sensor-influx" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-sensor-influx">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-sensor-influx" data-method="GET"
      data-path="api/sensor/influx"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-sensor-influx', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-sensor-influx"
                    onclick="tryItOut('GETapi-sensor-influx');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-sensor-influx"
                    onclick="cancelTryOut('GETapi-sensor-influx');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-sensor-influx"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/sensor/influx</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-sensor-influx"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-sensor-influx"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-sensor-influx"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sensor_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="sensor_id"                data-endpoint="GETapi-sensor-influx"
               value="1"
               data-component="body">
    <br>
<p>The ID of the sensor. Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>time_range</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="time_range"                data-endpoint="GETapi-sensor-influx"
               value="30"
               data-component="body">
    <br>
<p>Time range to query influx db. Example: <code>30</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sensor_operation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="sensor_operation"                data-endpoint="GETapi-sensor-influx"
               value="T01"
               data-component="body">
    <br>
<p>The sensor operation. Example: <code>T01</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>field</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="field"                data-endpoint="GETapi-sensor-influx"
               value="CH"
               data-component="body">
    <br>
<p>The sensor field. Example: <code>CH</code></p>
        </div>
        </form>

                <h1 id="user-management">User management</h1>

    <p>APIs for managing users</p>

                                <h2 id="user-management-POSTapi-login">Authenticate a user</h2>

<p>
</p>

<p>This endpoint allows you to authenticate a user and issue a personal access token.</p>

<span id="example-requests-POSTapi-login">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://girasole.test/api/login" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"email\": \"xyz@example.com\",
    \"password\": \"|]|{+-\",
    \"device_name\": \"mobile_app\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/login"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "email": "xyz@example.com",
    "password": "|]|{+-",
    "device_name": "mobile_app"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/login'
payload = {
    "email": "xyz@example.com",
    "password": "|]|{+-",
    "device_name": "mobile_app"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/login';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'json' =&gt; [
            'email' =&gt; 'xyz@example.com',
            'password' =&gt; '|]|{+-',
            'device_name' =&gt; 'mobile_app',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-login">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Login successful&quot;,
    &quot;token&quot;: &quot;1|randomtokenstring&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;John Doe&quot;,
        &quot;email&quot;: &quot;xyz@example.com&quot;,
        &quot;created_at&quot;: &quot;2025-10-03T08:48:00Z&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Account is not active. Please contact an admin.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;email&quot;: [
            &quot;The email field is required.&quot;
        ],
        &quot;password&quot;: [
            &quot;The password field is required.&quot;
        ],
        &quot;device_name&quot;: [
            &quot;The device name field is required.&quot;
        ]
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (429):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Too many login attempts. Please try again in 60 seconds.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-login" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-login"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-login"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-login" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-login">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-login" data-method="POST"
      data-path="api/login"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-login', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-login"
                    onclick="tryItOut('POSTapi-login');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-login"
                    onclick="cancelTryOut('POSTapi-login');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-login"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/login</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-login"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="POSTapi-login"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-login"
               value="xyz@example.com"
               data-component="body">
    <br>
<p>The email of the user. Example: <code>xyz@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-login"
               value="|]|{+-"
               data-component="body">
    <br>
<p>The password of the user. Example: <code>|]|{+-</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-login"
               value="mobile_app"
               data-component="body">
    <br>
<p>The name of the device that's connecting. Example: <code>mobile_app</code></p>
        </div>
        </form>

                    <h2 id="user-management-POSTapi-register">Register a new user.</h2>

<p>
</p>

<p>This endpoint creates a new user account, including billing address and billing information.
The user is created with active status set to false, pending admin activation.
Requires a subscription if active plans exist (post-registration).</p>

<span id="example-requests-POSTapi-register">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://girasole.test/api/register" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"first_name\": \"John\",
    \"last_name\": \"Doe\",
    \"email\": \"john.doe@example.com\",
    \"password\": \"password123\",
    \"type\": \"business\",
    \"business_name\": \"Doe Enterprises\",
    \"fiscal_code\": \"ABC1234567890123\",
    \"vat_number\": \"IT12345678901\",
    \"mobile_phone\": \"+1234567890\",
    \"sdi_code\": \"ABC1234\",
    \"street\": \"Main Street\",
    \"street_number\": \"123\",
    \"postal_code\": \"12345\",
    \"city_id\": 1,
    \"province_id\": 1,
    \"region_id\": 1,
    \"state\": \"NY\",
    \"device_name\": \"mobile_app\",
    \"password_confirmation\": \"password123\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/register"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "type": "business",
    "business_name": "Doe Enterprises",
    "fiscal_code": "ABC1234567890123",
    "vat_number": "IT12345678901",
    "mobile_phone": "+1234567890",
    "sdi_code": "ABC1234",
    "street": "Main Street",
    "street_number": "123",
    "postal_code": "12345",
    "city_id": 1,
    "province_id": 1,
    "region_id": 1,
    "state": "NY",
    "device_name": "mobile_app",
    "password_confirmation": "password123"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/register'
payload = {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "password": "password123",
    "type": "business",
    "business_name": "Doe Enterprises",
    "fiscal_code": "ABC1234567890123",
    "vat_number": "IT12345678901",
    "mobile_phone": "+1234567890",
    "sdi_code": "ABC1234",
    "street": "Main Street",
    "street_number": "123",
    "postal_code": "12345",
    "city_id": 1,
    "province_id": 1,
    "region_id": 1,
    "state": "NY",
    "device_name": "mobile_app",
    "password_confirmation": "password123"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/register';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'json' =&gt; [
            'first_name' =&gt; 'John',
            'last_name' =&gt; 'Doe',
            'email' =&gt; 'john.doe@example.com',
            'password' =&gt; 'password123',
            'type' =&gt; 'business',
            'business_name' =&gt; 'Doe Enterprises',
            'fiscal_code' =&gt; 'ABC1234567890123',
            'vat_number' =&gt; 'IT12345678901',
            'mobile_phone' =&gt; '+1234567890',
            'sdi_code' =&gt; 'ABC1234',
            'street' =&gt; 'Main Street',
            'street_number' =&gt; '123',
            'postal_code' =&gt; '12345',
            'city_id' =&gt; 1,
            'province_id' =&gt; 1,
            'region_id' =&gt; 1,
            'state' =&gt; 'NY',
            'device_name' =&gt; 'mobile_app',
            'password_confirmation' =&gt; 'password123',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-register">
            <blockquote>
            <p>Example response (201):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;User registered successfully. An admin will activate your account shortly.&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;John Doe&quot;,
        &quot;email&quot;: &quot;john.doe@example.com&quot;
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;email&quot;: [
            &quot;The email has already been taken.&quot;
        ],
        &quot;password&quot;: [
            &quot;The password field is required.&quot;
        ],
        &quot;business_name&quot;: [
            &quot;The business name field is required when type is business.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-register" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-register"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-register"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-register" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-register">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-register" data-method="POST"
      data-path="api/register"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-register', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-register"
                    onclick="tryItOut('POSTapi-register');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-register"
                    onclick="cancelTryOut('POSTapi-register');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-register"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/register</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-register"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="POSTapi-register"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="first_name"                data-endpoint="POSTapi-register"
               value="John"
               data-component="body">
    <br>
<p>The user's first name. Example: <code>John</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="last_name"                data-endpoint="POSTapi-register"
               value="Doe"
               data-component="body">
    <br>
<p>The user's last name. Example: <code>Doe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-register"
               value="john.doe@example.com"
               data-component="body">
    <br>
<p>The user's email address (must be unique). Example: <code>john.doe@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password"                data-endpoint="POSTapi-register"
               value="password123"
               data-component="body">
    <br>
<p>The user's password (minimum 8 characters, confirmed). Example: <code>password123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="POSTapi-register"
               value="business"
               data-component="body">
    <br>
<p>The fiscal type (business or sole_business). Example: <code>business</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>business_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="business_name"                data-endpoint="POSTapi-register"
               value="Doe Enterprises"
               data-component="body">
    <br>
<p>nullable The business name (required if type is business). Example: <code>Doe Enterprises</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>fiscal_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="fiscal_code"                data-endpoint="POSTapi-register"
               value="ABC1234567890123"
               data-component="body">
    <br>
<p>The fiscal code (max 16 characters). Example: <code>ABC1234567890123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>vat_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="vat_number"                data-endpoint="POSTapi-register"
               value="IT12345678901"
               data-component="body">
    <br>
<p>nullable The VAT number (required if type is business, max 20 characters). Example: <code>IT12345678901</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mobile_phone</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="mobile_phone"                data-endpoint="POSTapi-register"
               value="+1234567890"
               data-component="body">
    <br>
<p>The user's mobile phone number (max 20 characters). Example: <code>+1234567890</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sdi_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="sdi_code"                data-endpoint="POSTapi-register"
               value="ABC1234"
               data-component="body">
    <br>
<p>nullable The SDI code (required if type is business, max 7 characters). Example: <code>ABC1234</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>street</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="street"                data-endpoint="POSTapi-register"
               value="Main Street"
               data-component="body">
    <br>
<p>The street name of the billing address. Example: <code>Main Street</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>street_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="street_number"                data-endpoint="POSTapi-register"
               value="123"
               data-component="body">
    <br>
<p>The street number of the billing address. Example: <code>123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>postal_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="postal_code"                data-endpoint="POSTapi-register"
               value="12345"
               data-component="body">
    <br>
<p>The postal code (must exist in postal_codes table). Example: <code>12345</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>city_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="city_id"                data-endpoint="POSTapi-register"
               value="1"
               data-component="body">
    <br>
<p>The city ID (must exist in cities table). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>province_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="province_id"                data-endpoint="POSTapi-register"
               value="1"
               data-component="body">
    <br>
<p>The province ID (must exist in provinces table). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>region_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
 &nbsp;
                <input type="number" style="display: none"
               step="any"               name="region_id"                data-endpoint="POSTapi-register"
               value="1"
               data-component="body">
    <br>
<p>The region ID (must exist in regions table). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>state</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="state"                data-endpoint="POSTapi-register"
               value="NY"
               data-component="body">
    <br>
<p>The state code (max 2 characters). Example: <code>NY</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>device_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="device_name"                data-endpoint="POSTapi-register"
               value="mobile_app"
               data-component="body">
    <br>
<p>The device name. Example: <code>mobile_app</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>password_confirmation</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="password_confirmation"                data-endpoint="POSTapi-register"
               value="password123"
               data-component="body">
    <br>
<p>Confirmation of the password. Example: <code>password123</code></p>
        </div>
        </form>

                    <h2 id="user-management-POSTapi-password-reset">Send a password reset link to the user&#039;s email address.</h2>

<p>
</p>

<p>This endpoint sends a password reset link to the provided email address if it exists.</p>

<span id="example-requests-POSTapi-password-reset">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request POST \
    "http://girasole.test/api/password/reset" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"email\": \"xyz@Example.com\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/password/reset"
);

const headers = {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "email": "xyz@Example.com"
};

fetch(url, {
    method: "POST",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/password/reset'
payload = {
    "email": "xyz@Example.com"
}
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('POST', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/password/reset';
$response = $client-&gt;post(
    $url,
    [
        'headers' =&gt; [
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'json' =&gt; [
            'email' =&gt; 'xyz@Example.com',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-POSTapi-password-reset">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Password reset link sent to your email&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (400):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unable to send recovery link&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;email&quot;: [
            &quot;The email field is required.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-POSTapi-password-reset" hidden>
    <blockquote>Received response<span
                id="execution-response-status-POSTapi-password-reset"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-POSTapi-password-reset"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-POSTapi-password-reset" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-POSTapi-password-reset">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-POSTapi-password-reset" data-method="POST"
      data-path="api/password/reset"
      data-authed="0"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('POSTapi-password-reset', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-POSTapi-password-reset"
                    onclick="tryItOut('POSTapi-password-reset');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-POSTapi-password-reset"
                    onclick="cancelTryOut('POSTapi-password-reset');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-POSTapi-password-reset"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-black">POST</small>
            <b><code>api/password/reset</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="POSTapi-password-reset"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="POSTapi-password-reset"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="POSTapi-password-reset"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="POSTapi-password-reset"
               value="xyz@Example.com"
               data-component="body">
    <br>
<p>The user's email address. Example: <code>xyz@Example.com</code></p>
        </div>
        </form>

                    <h2 id="user-management-GETapi-me">Retrieve the authenticated user&#039;s profile and associated data.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>This endpoint returns the currently authenticated user's profile along with related information
such as billing info, billing address, shipping address, events, cadastral group, terms and conditions,
company details, and sensors.</p>

<span id="example-requests-GETapi-me">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request GET \
    --get "http://girasole.test/api/me" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}"</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/me"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

fetch(url, {
    method: "GET",
    headers,
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/me'
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('GET', url, headers=headers)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/me';
$response = $client-&gt;get(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-GETapi-me">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
  &quot;message&quot;: &quot;Profile successfully loaded.&quot;,
  &quot;user&quot;: {
      &quot;id&quot;: 1,
      &quot;name&quot;: &quot;Example User&quot;,
      ...
  }
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
    </span>
<span id="execution-results-GETapi-me" hidden>
    <blockquote>Received response<span
                id="execution-response-status-GETapi-me"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-GETapi-me"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-GETapi-me" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-GETapi-me">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-GETapi-me" data-method="GET"
      data-path="api/me"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('GETapi-me', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-GETapi-me"
                    onclick="tryItOut('GETapi-me');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-GETapi-me"
                    onclick="cancelTryOut('GETapi-me');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-GETapi-me"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-green">GET</small>
            <b><code>api/me</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="GETapi-me"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="GETapi-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="GETapi-me"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="GETapi-me"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                        </form>

                    <h2 id="user-management-PATCHapi-update-profile">Update the authenticated user&#039;s profile, billing info, billing address, and/or shipping address.</h2>

<p>
<small class="badge badge-darkred">requires authentication</small>
</p>

<p>This endpoint allows partial updates to any combination of user profile, billing information,
billing address, and shipping address. All fields are optional.</p>

<span id="example-requests-PATCHapi-update-profile">
<blockquote>Example request:</blockquote>


<div class="bash-example">
    <pre><code class="language-bash">curl --request PATCH \
    "http://girasole.test/api/update/profile" \
    --header "Authorization: Bearer {YOUR_AUTH_KEY}" \
    --header "Content-Type: application/json" \
    --header "Accept: application/json" \
    --header "X-App-Authentication: {YOUR_APP_TOKEN}" \
    --data "{
    \"first_name\": \"John\",
    \"last_name\": \"Doe\",
    \"email\": \"john.doe@example.com\",
    \"mobile_number\": \"+1234567890\",
    \"type\": \"business\",
    \"business_name\": \"Doe Enterprises\",
    \"fiscal_code\": \"ABC1234567890123\",
    \"vat_number\": \"IT12345678901\",
    \"sdi_code\": \"ABC1234\",
    \"street\": \"Main Street\",
    \"street_number\": \"123\",
    \"postal_code\": \"12345\",
    \"city_id\": 1,
    \"province_id\": 1,
    \"region_id\": 1,
    \"state\": \"NY\",
    \"same_as_billing\": true,
    \"shipping_region_id\": 1,
    \"shipping_province_id\": 1,
    \"shipping_postal_code\": \"12345\",
    \"shipping_city_id\": 1,
    \"name\": \"John Doe\",
    \"address\": \"456 Oak Ave\",
    \"cellular\": \"+1234567890\"
}"
</code></pre></div>


<div class="javascript-example">
    <pre><code class="language-javascript">const url = new URL(
    "http://girasole.test/api/update/profile"
);

const headers = {
    "Authorization": "Bearer {YOUR_AUTH_KEY}",
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-App-Authentication": "{YOUR_APP_TOKEN}",
};

let body = {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "mobile_number": "+1234567890",
    "type": "business",
    "business_name": "Doe Enterprises",
    "fiscal_code": "ABC1234567890123",
    "vat_number": "IT12345678901",
    "sdi_code": "ABC1234",
    "street": "Main Street",
    "street_number": "123",
    "postal_code": "12345",
    "city_id": 1,
    "province_id": 1,
    "region_id": 1,
    "state": "NY",
    "same_as_billing": true,
    "shipping_region_id": 1,
    "shipping_province_id": 1,
    "shipping_postal_code": "12345",
    "shipping_city_id": 1,
    "name": "John Doe",
    "address": "456 Oak Ave",
    "cellular": "+1234567890"
};

fetch(url, {
    method: "PATCH",
    headers,
    body: JSON.stringify(body),
}).then(response =&gt; response.json());</code></pre></div>


<div class="python-example">
    <pre><code class="language-python">import requests
import json

url = 'http://girasole.test/api/update/profile'
payload = {
    "first_name": "John",
    "last_name": "Doe",
    "email": "john.doe@example.com",
    "mobile_number": "+1234567890",
    "type": "business",
    "business_name": "Doe Enterprises",
    "fiscal_code": "ABC1234567890123",
    "vat_number": "IT12345678901",
    "sdi_code": "ABC1234",
    "street": "Main Street",
    "street_number": "123",
    "postal_code": "12345",
    "city_id": 1,
    "province_id": 1,
    "region_id": 1,
    "state": "NY",
    "same_as_billing": true,
    "shipping_region_id": 1,
    "shipping_province_id": 1,
    "shipping_postal_code": "12345",
    "shipping_city_id": 1,
    "name": "John Doe",
    "address": "456 Oak Ave",
    "cellular": "+1234567890"
}
headers = {
  'Authorization': 'Bearer {YOUR_AUTH_KEY}',
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'X-App-Authentication': '{YOUR_APP_TOKEN}'
}

response = requests.request('PATCH', url, headers=headers, json=payload)
response.json()</code></pre></div>


<div class="php-example">
    <pre><code class="language-php">$client = new \GuzzleHttp\Client();
$url = 'http://girasole.test/api/update/profile';
$response = $client-&gt;patch(
    $url,
    [
        'headers' =&gt; [
            'Authorization' =&gt; 'Bearer {YOUR_AUTH_KEY}',
            'Content-Type' =&gt; 'application/json',
            'Accept' =&gt; 'application/json',
            'X-App-Authentication' =&gt; '{YOUR_APP_TOKEN}',
        ],
        'json' =&gt; [
            'first_name' =&gt; 'John',
            'last_name' =&gt; 'Doe',
            'email' =&gt; 'john.doe@example.com',
            'mobile_number' =&gt; '+1234567890',
            'type' =&gt; 'business',
            'business_name' =&gt; 'Doe Enterprises',
            'fiscal_code' =&gt; 'ABC1234567890123',
            'vat_number' =&gt; 'IT12345678901',
            'sdi_code' =&gt; 'ABC1234',
            'street' =&gt; 'Main Street',
            'street_number' =&gt; '123',
            'postal_code' =&gt; '12345',
            'city_id' =&gt; 1,
            'province_id' =&gt; 1,
            'region_id' =&gt; 1,
            'state' =&gt; 'NY',
            'same_as_billing' =&gt; true,
            'shipping_region_id' =&gt; 1,
            'shipping_province_id' =&gt; 1,
            'shipping_postal_code' =&gt; '12345',
            'shipping_city_id' =&gt; 1,
            'name' =&gt; 'John Doe',
            'address' =&gt; '456 Oak Ave',
            'cellular' =&gt; '+1234567890',
        ],
    ]
);
$body = $response-&gt;getBody();
print_r(json_decode((string) $body));</code></pre></div>

</span>

<span id="example-responses-PATCHapi-update-profile">
            <blockquote>
            <p>Example response (200):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Profile updated successfully&quot;,
    &quot;user&quot;: {
        &quot;id&quot;: 1,
        &quot;name&quot;: &quot;John Doe&quot;,
        &quot;first_name&quot;: &quot;John&quot;,
        &quot;last_name&quot;: &quot;Doe&quot;,
        &quot;email&quot;: &quot;john.doe@example.com&quot;,
        &quot;mobile_number&quot;: &quot;+1234567890&quot;,
        &quot;billing_info&quot;: {
            &quot;id&quot;: 1,
            &quot;fiscal_type&quot;: &quot;business&quot;,
            &quot;fiscal_code&quot;: &quot;ABC1234567890123&quot;,
            &quot;sdi&quot;: &quot;ABC1234&quot;,
            &quot;vat_number&quot;: &quot;IT12345678901&quot;,
            &quot;business_name&quot;: &quot;Doe Enterprises&quot;
        },
        &quot;billing_address&quot;: {
            &quot;id&quot;: 1,
            &quot;street&quot;: &quot;Main Street&quot;,
            &quot;street_number&quot;: &quot;123&quot;,
            &quot;postal_code&quot;: &quot;12345&quot;,
            &quot;city&quot;: &quot;New York&quot;,
            &quot;province&quot;: &quot;NYC&quot;,
            &quot;region&quot;: &quot;NY&quot;,
            &quot;state&quot;: &quot;NY&quot;
        },
        &quot;shipping_address&quot;: {
            &quot;id&quot;: 1,
            &quot;name&quot;: &quot;John Doe&quot;,
            &quot;address&quot;: &quot;456 Oak Ave&quot;,
            &quot;postal_code&quot;: &quot;12345&quot;,
            &quot;city&quot;: &quot;New York&quot;,
            &quot;province&quot;: &quot;NYC&quot;,
            &quot;region&quot;: &quot;NY&quot;,
            &quot;phone_number&quot;: &quot;+1234567890&quot;,
            &quot;same_as_billing&quot;: false
        }
    }
}</code>
 </pre>
            <blockquote>
            <p>Example response (401):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Unauthenticated&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (403):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Technicians are not allowed to access this endpoint.&quot;
}</code>
 </pre>
            <blockquote>
            <p>Example response (422):</p>
        </blockquote>
                <pre>

<code class="language-json" style="max-height: 300px;">{
    &quot;message&quot;: &quot;Validation failed&quot;,
    &quot;errors&quot;: {
        &quot;email&quot;: [
            &quot;The email has already been taken.&quot;
        ],
        &quot;business_name&quot;: [
            &quot;The business name field is required when type is business.&quot;
        ]
    }
}</code>
 </pre>
    </span>
<span id="execution-results-PATCHapi-update-profile" hidden>
    <blockquote>Received response<span
                id="execution-response-status-PATCHapi-update-profile"></span>:
    </blockquote>
    <pre class="json"><code id="execution-response-content-PATCHapi-update-profile"
      data-empty-response-text="<Empty response>" style="max-height: 400px;"></code></pre>
</span>
<span id="execution-error-PATCHapi-update-profile" hidden>
    <blockquote>Request failed with error:</blockquote>
    <pre><code id="execution-error-message-PATCHapi-update-profile">

Tip: Check that you&#039;re properly connected to the network.
If you&#039;re a maintainer of ths API, verify that your API is running and you&#039;ve enabled CORS.
You can check the Dev Tools console for debugging information.</code></pre>
</span>
<form id="form-PATCHapi-update-profile" data-method="PATCH"
      data-path="api/update/profile"
      data-authed="1"
      data-hasfiles="0"
      data-isarraybody="0"
      autocomplete="off"
      onsubmit="event.preventDefault(); executeTryOut('PATCHapi-update-profile', this);">
    <h3>
        Request&nbsp;&nbsp;&nbsp;
                    <button type="button"
                    style="background-color: #8fbcd4; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-tryout-PATCHapi-update-profile"
                    onclick="tryItOut('PATCHapi-update-profile');">Try it out ⚡
            </button>
            <button type="button"
                    style="background-color: #c97a7e; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-canceltryout-PATCHapi-update-profile"
                    onclick="cancelTryOut('PATCHapi-update-profile');" hidden>Cancel 🛑
            </button>&nbsp;&nbsp;
            <button type="submit"
                    style="background-color: #6ac174; padding: 5px 10px; border-radius: 5px; border-width: thin;"
                    id="btn-executetryout-PATCHapi-update-profile"
                    data-initial-text="Send Request 💥"
                    data-loading-text="⏱ Sending..."
                    hidden>Send Request 💥
            </button>
            </h3>
            <p>
            <small class="badge badge-purple">PATCH</small>
            <b><code>api/update/profile</code></b>
        </p>
                <h4 class="fancy-heading-panel"><b>Headers</b></h4>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Authorization</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Authorization" class="auth-value"               data-endpoint="PATCHapi-update-profile"
               value="Bearer {YOUR_AUTH_KEY}"
               data-component="header">
    <br>
<p>Example: <code>Bearer {YOUR_AUTH_KEY}</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Content-Type</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Content-Type"                data-endpoint="PATCHapi-update-profile"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>Accept</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="Accept"                data-endpoint="PATCHapi-update-profile"
               value="application/json"
               data-component="header">
    <br>
<p>Example: <code>application/json</code></p>
            </div>
                                <div style="padding-left: 28px; clear: unset;">
                <b style="line-height: 2;"><code>X-App-Authentication</code></b>&nbsp;&nbsp;
&nbsp;
 &nbsp;
                <input type="text" style="display: none"
                              name="X-App-Authentication"                data-endpoint="PATCHapi-update-profile"
               value="{YOUR_APP_TOKEN}"
               data-component="header">
    <br>
<p>Example: <code>{YOUR_APP_TOKEN}</code></p>
            </div>
                                <h4 class="fancy-heading-panel"><b>Body Parameters</b></h4>
        <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>first_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="first_name"                data-endpoint="PATCHapi-update-profile"
               value="John"
               data-component="body">
    <br>
<p>nullable The user's first name (max 255 chars). Example: <code>John</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>last_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="last_name"                data-endpoint="PATCHapi-update-profile"
               value="Doe"
               data-component="body">
    <br>
<p>nullable The user's last name (max 255 chars). Example: <code>Doe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>email</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="email"                data-endpoint="PATCHapi-update-profile"
               value="john.doe@example.com"
               data-component="body">
    <br>
<p>nullable The user's email (unique, lowercase, max 255 chars). Example: <code>john.doe@example.com</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>mobile_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="mobile_number"                data-endpoint="PATCHapi-update-profile"
               value="+1234567890"
               data-component="body">
    <br>
<p>nullable The user's mobile number (max 255 chars). Example: <code>+1234567890</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>type</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="type"                data-endpoint="PATCHapi-update-profile"
               value="business"
               data-component="body">
    <br>
<p>nullable The fiscal type (business or sole_business, max 50 chars). Example: <code>business</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>business_name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="business_name"                data-endpoint="PATCHapi-update-profile"
               value="Doe Enterprises"
               data-component="body">
    <br>
<p>nullable The business name (max 255 chars, required if type=business). Example: <code>Doe Enterprises</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>fiscal_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="fiscal_code"                data-endpoint="PATCHapi-update-profile"
               value="ABC1234567890123"
               data-component="body">
    <br>
<p>nullable The fiscal code (max 16 chars). Example: <code>ABC1234567890123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>vat_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="vat_number"                data-endpoint="PATCHapi-update-profile"
               value="IT12345678901"
               data-component="body">
    <br>
<p>nullable The VAT number (max 20 chars, required if type=business). Example: <code>IT12345678901</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>sdi_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="sdi_code"                data-endpoint="PATCHapi-update-profile"
               value="ABC1234"
               data-component="body">
    <br>
<p>nullable The SDI code (max 7 chars, required if type=business). Example: <code>ABC1234</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>street</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="street"                data-endpoint="PATCHapi-update-profile"
               value="Main Street"
               data-component="body">
    <br>
<p>nullable The billing street name (max 255 chars). Example: <code>Main Street</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>street_number</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="street_number"                data-endpoint="PATCHapi-update-profile"
               value="123"
               data-component="body">
    <br>
<p>nullable The billing street number (max 255 chars). Example: <code>123</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>postal_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="postal_code"                data-endpoint="PATCHapi-update-profile"
               value="12345"
               data-component="body">
    <br>
<p>nullable The billing postal code (max 10 chars, must exist). Example: <code>12345</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>city_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="city_id"                data-endpoint="PATCHapi-update-profile"
               value="1"
               data-component="body">
    <br>
<p>nullable The billing city ID (must exist). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>province_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="province_id"                data-endpoint="PATCHapi-update-profile"
               value="1"
               data-component="body">
    <br>
<p>nullable The billing province ID (must exist). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>region_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="region_id"                data-endpoint="PATCHapi-update-profile"
               value="1"
               data-component="body">
    <br>
<p>nullable The billing region ID (must exist). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>state</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="state"                data-endpoint="PATCHapi-update-profile"
               value="NY"
               data-component="body">
    <br>
<p>nullable The billing state code (max 2 chars). Example: <code>NY</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>same_as_billing</code></b>&nbsp;&nbsp;
<small>boolean</small>&nbsp;
<i>optional</i> &nbsp;
                <label data-endpoint="PATCHapi-update-profile" style="display: none">
            <input type="radio" name="same_as_billing"
                   value="true"
                   data-endpoint="PATCHapi-update-profile"
                   data-component="body"             >
            <code>true</code>
        </label>
        <label data-endpoint="PATCHapi-update-profile" style="display: none">
            <input type="radio" name="same_as_billing"
                   value="false"
                   data-endpoint="PATCHapi-update-profile"
                   data-component="body"             >
            <code>false</code>
        </label>
    <br>
<p>nullable Whether shipping address matches billing (default false). Example: <code>true</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>shipping_region_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="shipping_region_id"                data-endpoint="PATCHapi-update-profile"
               value="1"
               data-component="body">
    <br>
<p>nullable The shipping region ID (required if same_as_billing=false, must exist). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>shipping_province_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="shipping_province_id"                data-endpoint="PATCHapi-update-profile"
               value="1"
               data-component="body">
    <br>
<p>nullable The shipping province ID (required if same_as_billing=false, must exist). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>shipping_postal_code</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="shipping_postal_code"                data-endpoint="PATCHapi-update-profile"
               value="12345"
               data-component="body">
    <br>
<p>nullable The shipping postal code (max 10 chars, required if same_as_billing=false). Example: <code>12345</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>shipping_city_id</code></b>&nbsp;&nbsp;
<small>integer</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="number" style="display: none"
               step="any"               name="shipping_city_id"                data-endpoint="PATCHapi-update-profile"
               value="1"
               data-component="body">
    <br>
<p>nullable The shipping city ID (required if same_as_billing=false, must exist). Example: <code>1</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>name</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="name"                data-endpoint="PATCHapi-update-profile"
               value="John Doe"
               data-component="body">
    <br>
<p>nullable The shipping name (required if same_as_billing=false). Example: <code>John Doe</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>address</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="address"                data-endpoint="PATCHapi-update-profile"
               value="456 Oak Ave"
               data-component="body">
    <br>
<p>nullable The shipping address (required if same_as_billing=false). Example: <code>456 Oak Ave</code></p>
        </div>
                <div style=" padding-left: 28px;  clear: unset;">
            <b style="line-height: 2;"><code>cellular</code></b>&nbsp;&nbsp;
<small>string</small>&nbsp;
<i>optional</i> &nbsp;
                <input type="text" style="display: none"
                              name="cellular"                data-endpoint="PATCHapi-update-profile"
               value="+1234567890"
               data-component="body">
    <br>
<p>nullable The shipping phone number (required if same_as_billing=false). Example: <code>+1234567890</code></p>
        </div>
        </form>

            

        
    </div>
    <div class="dark-box">
                    <div class="lang-selector">
                                                        <button type="button" class="lang-button" data-language-name="bash">bash</button>
                                                        <button type="button" class="lang-button" data-language-name="javascript">javascript</button>
                                                        <button type="button" class="lang-button" data-language-name="python">python</button>
                                                        <button type="button" class="lang-button" data-language-name="php">php</button>
                            </div>
            </div>
</div>
</body>
</html>
