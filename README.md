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

### Where a radio unit stands

The registered coordinates are compared against the place the unit is recorded
at, which is either an access point of yours or a customer connection — the
client end of a link is at the customer, and there is no mast of yours to name
for it. A unit stands in one place, so fill in one or the other, not both; the
access point is the one compared against if both are there.

Units recorded before the customer connection was offered can be placed from
the devices carrying them — a unit is matched to its device by the serial
number, and the device has usually been placed already:

```sh
bin/cake radio_units_link_customers --dry-run   # say what would be placed
bin/cake radio_units_link_customers
```

It only fills in units that say nothing about where they stand, never
overwrites, and reports the units it could not place. There is nothing to
schedule — a unit recorded from then on is placed as it is recorded.

## Planned power outages (ČEZ Distribuce)

An access point stands on somebody else's electricity, and the first anybody
usually hears of a planned outage is the mast going quiet. The outages ČEZ
Distribuce publishes are read on a schedule and matched against your access
points, so that a mast due to lose power says so on its own page and on the
dashboard beforehand.

This is **off unless you turn it on**. What is read are undocumented endpoints
of the public outage widget rather than an interface anybody promised — they
carry no contract and can change without notice. Publishing planned outages is
a duty laid on the distributor and nothing in its terms or its `robots.txt`
forbids reading them, but read gently and say who you are:

```sh
export POWER_OUTAGES_ENABLED="true"
export POWER_OUTAGES_USER_AGENT="Watcher NMS (nms@example.com)"
```

Reading it also needs the address registry configured (`ADDRESSES_API_URL`),
which is the same one the address whisperer asks. The registry is what turns
the coordinates of a mast into the number the distributor keeps its
municipality under.

Read the outages on a schedule, e.g. daily and early, and tell the operators
what is coming on the mornings somebody is there to act on it:

```sh
# read what has been published, before anybody is at a desk
17 4 * * *   cd /srv/watcher-nms && bin/cake power_outages_update >/dev/null

# say what is coming; silent on the days there is nothing
5 7 * * 1-5  cd /srv/watcher-nms && bin/cake power_outages_report >/dev/null
```

The report sends nothing when nothing is coming, so it can be run daily without
becoming the sort of mail people set a rule to file away unread. Outages also
appear on the dashboard, and on the page of each access point they are over.

### Fill in the supply point

There are two ways an outage can be matched to a mast, and they are not worth
the same.

Where the **EAN of the supply point** is filled in on the access point — the
eighteen digits off the electricity bill — the distributor is asked about that
supply point directly, and what it answers is about that mast and nothing else.
Such an outage is shown as *certain*, and it is also the only way a withdrawn
outage is ever noticed.

Where it is not, the addresses nearest the mast stand in for it: the
municipality is asked instead, and an outage is taken to be about the mast when
it reaches one of those addresses. That is a good guess — the power reaching a
mast usually comes from the buildings around it — but no more than a guess, so
it is shown as *probable*, together with which address it was matched against
and how far off that address stands. A mast on a roof draws from the building
under it, which may be on another street; one standing in a field draws from a
line the distributor lists by parcel, which is not looked at.

So: a mast standing away from any village will report nothing at all until its
supply point is filled in, and its page says so rather than showing an empty
list.

### When something looks wrong

```sh
# do all of it and keep none of it
bin/cake power_outages_update --dry-run

# one mast, to see what it is being told
bin/cake power_outages_update --access-point <uuid> --dry-run

# work every link out again from what is stored, asking nobody anything
bin/cake power_outages_update --rematch

# after changing the radius or the number of addresses kept
bin/cake power_outages_update --force-resolve
```

`--file` replays answers that were kept, in a file keyed by the question each
one answers — `{"town:533165": {…}, "ean:859…": {…}}`. That is what to send
along with a report that the reading has gone wrong.

How far around a mast an address may lie, how many are kept, and how long an
outage is kept after it has happened are all settings under *Access Points →
Power Outages*.

The access point page also links to the distributor's own pages — the one asking
whether the power is off **now**, which is a question no server of ours may ask
because it sits behind a check for humans, and the one listing what is planned,
which stays the place to settle an argument about what our listing says.

Both arrive with the place already in them, by the registry number of the
nearest address: the widget on either page starts from the same number the
address registry hands us. That parameter is not documented anywhere, so if it
ever stops working the pages still open and the address stands written out on
the row above.

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

Later updates of such an installation are one script:

```bash
./update.sh
```

It refuses to run on a tree with local changes, then pulls, installs, migrates,
relinks the plugin assets and clears the caches. Where opcache is configured not to
check file timestamps, set `PHP_FPM_SERVICE` to the service name and the script
reloads the pool at the end.

## Configuration

Runtime settings live in `config/.env` (or are passed in as environment
variables — see the `environment:` blocks in the compose files for the keys
read at boot). Common groups:

- **Database / cache:** `DATABASE_URL`, `CACHE_*_URL`
- **Server:** `APP_NAME` (used as cache prefix), `SERVER_NAME` (domain for
  ACME / TLS in the production image)
- **Maps:** `NOMINATIM_*` and `PHOTON_URL` name the services behind the address
  search and reverse geocoding. See below.

### Maps

The maps are drawn by the `Maps` plugin in `plugins/Maps` — Leaflet with
OpenStreetMap tiles, vendored so a map needs nothing from a CDN. `MAP_PROVIDER`
selects the mapping stack and `osm` is the only one built in; see the plugin's
README for what adding another one takes.

Addresses come from two OpenStreetMap services:
[Photon](https://photon.komoot.io) answers the search box while you type, and
[Nominatim](https://nominatim.openstreetmap.org) says what a pair of coordinates
falls on. No API key and no billing. The browser asks neither directly — it asks
this application, which keeps the choice of geocoder, and any key it needs, on
the server.

Both default to the public community servers. They are fine for a normal
installation (results are cached per access point), but their usage policies
expect low volume and a `User-Agent` identifying your installation — set
`NOMINATIM_USER_AGENT` to something like `Watcher NMS (nms@example.com)`. Both
are self-hostable; point `NOMINATIM_URL` and `PHOTON_URL` at your own instances
to lift the public rate limits.

#### Aerial imagery

OpenStreetMap is map data, not photography, so the imagery comes from separate
services. The maps carry a layer switcher offering:

- **OpenStreetMap** — the street map, shown on load
- **Ortofoto ČR (ČÚZK)** — the Czech national orthophoto, free and keyless, up
  to zoom 20, Czech Republic only
- **Satellite (Esri)** — Esri World Imagery, worldwide coverage

The list lives under `Maps.baseLayers`, which the plugin ships in
`plugins/Maps/config/maps.php` and `config/app.php` may override. Each entry is
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
