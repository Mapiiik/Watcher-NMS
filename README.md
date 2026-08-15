# Watcher NMS

[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen.svg?style=flat-square)](https://github.com/phpstan/phpstan)

Network Management System for ISPs, built on [CakePHP](https://github.com/cakephp/cakephp).

## Description

- Inventory of access points, network devices, electricity-meter readings, …
- RouterOS device auto-registration via SNMP — devices identify themselves
  through the [Watcher Agent](https://github.com/Mapiiik/Watcher-Agent), which
  reads them over SNMP and pushes the inventory here; the matching device-type
  profile is then applied automatically.
- Optional checks for radio interference with Czech weather radar stations.

## RouterOS device integration

RouterOS device provisioning runs through the
[Watcher Agent](https://github.com/Mapiiik/Watcher-Agent) — a small on-site
service that can reach customer-edge devices the NMS cannot talk to directly.
The agent reads the device over SNMP, pushes the inventory to this NMS, and
returns the generated configuration script back to the device.

Configure a scheduler on the RouterOS device to fetch and run the script from
the agent. The URL embeds the device-type identifier and the device's serial
number; the agent verifies the serial over SNMP and the NMS returns the
appropriate script response.

```routeros
/tool fetch url=( \
    "https://agent.watcher.domain/provision/routeros/{device-type-identifier}/" \
        . [/system routerboard get serial-number] \
        . "/\?token=***" \
    ) dst-path=watcher-config.rsc
/import watcher-config.rsc
:delay 5
/file remove watcher-config.rsc
```

The `?token=***` must match the agent's `AGENT_ROUTEROS_QUERY_TOKEN`, and the
device's source IP must fall within the agent's `AGENT_ROUTEROS_ALLOW_CIDRS`
allowlist (see the [Watcher Agent](https://github.com/Mapiiik/Watcher-Agent)
configuration).

To also update the admin password (derived from the serial number and the system‑wide salt), enable this option for the device type in the web UI and ensure the script is imported after fetching.

The currently generated password for each device is shown on its detail page.

Otherwise the script only logs the provisioning status and performs no changes.

## Register of stations (ČTÚ RLAN portal)

Radio units on the bands operated under the general authorisation VO-R/12 are
checked against the register the regulator keeps at
[rlan.ctu.gov.cz](https://rlan.ctu.gov.cz). Two overviews report the result —
radio units against the stations registered for them, and the stations against
the units that record them. Nothing is ever written back to the portal; an
ordinary portal account that may only look is enough.

Set the account in `config/.env`:

```sh
export RLAN_EMAIL=""
export RLAN_PASSWORD=""
# the account number your stations are registered to; the listing also carries
# the stations of every account that shares with yours
export RLAN_USER_ID=""
```

Then say which bands are registered — tick **Units Require RLAN Registration**
on each band under *Radio Unit Bands*. Nothing is reported for a band until it
is, so an installation that has not got round to it sees an empty listing
rather than a wall of findings.

Read the register on a schedule, e.g. daily:

```sh
bin/cake rlan_stations_update
```

A radio unit is matched to a station by the MAC address it is recorded under
(`station_address`), and failing that by the number the registration was filed
under (`authorization_number`). The second is the name the portal shows for the
station — both ends of a point-to-point link share one, so the address is what
tells them apart.

Only the 60 GHz bands publish technical parameters; the 5.2 and 5.8 GHz ones
are registered by coordinates and address alone, and the overview says so
rather than reporting them as mismatched.

## Requirements

- PHP 8.2 or newer
- PostgreSQL
- Redis
- [Watcher Agent](https://github.com/Mapiiik/Watcher-Agent) — required only for
  SNMP reads and RouterOS provisioning. It is a separate service (run it in
  Docker, even on the same host; it supports the PROXY protocol behind a load
  balancer). The NMS no longer talks SNMP directly, so the PHP `snmp` extension
  is no longer needed.

The Docker Compose stack below provides PostgreSQL and Redis out of the box,
so on a fresh host you only need Docker.

## Installation

Two install paths are supported. Docker Compose is recommended.

### Option A — Docker Compose (recommended)

```bash
git clone https://github.com/Mapiiik/Watcher-NMS.git
cd Watcher-NMS
cp config/.env.example config/.env
# edit config/.env — set APP_NAME and any integration URLs / API keys
docker compose -f compose.production.yaml up -d
```

The production image runs `composer run-script migrations` and rebuilds the
schema cache automatically on container start, so the app is reachable at
`http://localhost` (and `https://localhost` with a self-signed cert) once the
container is healthy. Set `SERVER_NAME` in the compose environment to a real
domain to enable Let's Encrypt issuance via the bundled `acme.sh`.

### Option B — Bare-metal (host nginx + PHP-FPM, FrankenPHP, …)

For hosts already running their own PHP webserver:

```bash
git clone https://github.com/Mapiiik/Watcher-NMS.git
cd Watcher-NMS
composer install --no-dev
cp config/.env.example config/.env
# edit config/.env — at minimum DATABASE_URL and CACHE_*_URL

composer run-script migrations
composer run-script schema-cache
```

Point your webserver's document root at the `webroot/` directory. SNMP polling
and RouterOS provisioning are handled by the separate
[Watcher Agent](https://github.com/Mapiiik/Watcher-Agent), so no PHP `snmp`
extension is required here.

## Configuration

Runtime settings live in `config/.env` (or are passed in as environment
variables — see the `environment:` blocks in the compose files for the keys
read at boot). Common groups:

- **Database / cache:** `DATABASE_URL`, `CACHE_*_URL`
- **Server:** `APP_NAME` (used as cache prefix), `SERVER_NAME` (domain for
  ACME / TLS in the production image)
- **Maps:** `MAP_PROVIDER` selects the whole mapping stack — maps, address
  search and reverse geocoding. See below.

### Map provider

`MAP_PROVIDER` accepts two values:

- `google` (default) — Maps JavaScript API for the maps, Places for the address
  search box and `geocoder-php/google-maps-provider` for reverse geocoding. All
  three need `GOOGLE_MAP_API_KEY`, and the key needs **billing enabled on the
  Google Cloud project**; without it Google refuses the requests.
- `osm` — Leaflet with OpenStreetMap tiles, [Photon](https://photon.komoot.io)
  for the address search box and [Nominatim](https://nominatim.openstreetmap.org)
  for reverse geocoding. No API key and no billing.

In `osm` mode everything defaults to the public community servers. They are fine
for a normal installation (results are cached per access point), but their usage
policies expect low volume and a `User-Agent` identifying your installation —
set `NOMINATIM_USER_AGENT` to something like `Watcher NMS (nms@example.com)`.
Both services are self-hostable; point `NOMINATIM_URL`, `PHOTON_URL` and
`MAP_TILE_URL` at your own instances to lift the public rate limits.

#### Aerial imagery

OpenStreetMap is map data, not photography, so `osm` mode gets its imagery from
separate services. The maps carry a layer switcher offering:

- **OpenStreetMap** — the street map, shown on load
- **Ortofoto ČR (ČÚZK)** — the Czech national orthophoto, free and keyless, up
  to zoom 20, Czech Republic only
- **Satellite (Esri)** — Esri World Imagery, worldwide coverage

The list lives under `Leaflet.baseLayers` in `config/app.php`. Each entry is
either `type: xyz` (a plain `{z}/{x}/{y}` tile service) or `type: wms`; add your
own services or drop the ones you do not need. The first entry is the layer
shown on load. Keep the `attribution` values — displaying them is a licence
condition of both imagery providers.

See `config/.env.example` for the full list of map related variables.

### Customizing the compose stack

If `compose.production.yaml` doesn't fit your environment, copy it to
`compose.yaml` and customize there — `compose.yaml` is git-ignored, so
`git pull` won't overwrite your changes.

```bash
cp compose.production.yaml compose.yaml
# edit compose.yaml as needed
docker compose up -d
```

Typical reasons to override: pointing services at infrastructure already
running on the host (e.g. an existing PostgreSQL instance, external Redis,
reverse proxy), removing bundled containers you don't need, or tweaking
volumes / networks.

## Development

Two compose files target local development:

- `compose.dev-frankenphp.yaml` — FrankenPHP (HTTP/1.1, HTTP/2, HTTP/3)
- `compose.dev-nginx.yaml` — classic nginx + PHP-FPM

Both bind-mount the working tree into the container and place `vendor/`,
`tmp/`, `logs/`, plus the PostgreSQL data directory and Redis data on
tmpfs — fast iteration and disposable state, but everything in those paths
is lost when the stack is torn down.

```bash
docker compose -f compose.dev-frankenphp.yaml up
```

The Postgres and Redis ports are exposed to the host (`5432`, `6379`) so you
can connect with local clients while the stack is running.

## License

Watcher NMS is licensed under the GNU Affero General Public License v3.0.
Copyright (c) 2026 Martin Patočka.

### What this means

You are free to use, modify and run this software. If you modify it and make
it available to others (including as a network service), you must also make
your modifications available under the same license.
