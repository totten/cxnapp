# cxnapp (MemberStatusBundle)

CiviCRM is supported through membership and partner programs.
To encourage organizations to participate as members and partners,
some `cxnapp` services may offer/adapt services based one's membership.

The `MemberStatusBundle` provides a utility to determine whether
a given connection corresponds to a member site, e.g.

```php
$checker = $container->get('memberships.checker');
if ($checker->checkCxn($cxnEntity)) {
  echo "Dear Member: Please have a *very* nice day!";
}
else {
  echo "Dear Non-Member: Please have a *moderately* nice day!";
}
```

The best way to think of `memberships.checker` is that it queries a
table like this:

<table>
  <thead>
    <tr>
      <th>url</th>
      <th>via_port</th>
      <th>is_active</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>http://example1.org</td>
      <td></td>
      <td>1</td>
    </tr>
    <tr>
      <td>http://example2.private</td>
      <td>proxy.example2.org:123</td>
      <td>1</td>
    </tr>
  </tbody>
</table>

To support different testing/staging/development configurations, the
table can be read from different sources:

 * `memberships.static` (a hard-coded list; useful for testing)
 * `memberships.csv` (a CSV file; `app/config/memberships.csv`)
 * `memberships.civicrmorg_sql` (the `civicrm.org` SQL database)
 * ~~`memberships.civicrmorg_http` (an HTTP service from `civicrm.org`)~~ (WIP)

To specify which source you want to use, edit `app/config/parameters.yml`
and set `memberships_source`.

## Tip: Working with `memberships.civicrmorg_sql`

This data-source reads entries from the `civicrm.org` MySQL database. If
developing/testing/using this data-source, you will need a few things:

 * In `parameters.yml`, define the connection details:
   * `civicrmorg_database_host`
   * `civicrmorg_database_port`
   * `civicrmorg_database_name`
   * `civicrmorg_database_user`
   * `civicrmorg_database_password`
 * Create a SQL view named `cxn_member_urls`, e.g.

```sql
CREATE VIEW cxn_member_urls
SELECT cstm.member_site_216 AS url, null AS via_port, status.is_current_member AS is_active
FROM civicrm_membership m
INNER JOIN civicrm_membership_status status ON m.status_id = status.id
INNER JOIN civicrm_value_sid_22 cstm ON cstm.entity_id = m.contact_id
```

This view should return three columns (`url`, `via_port`, and `is_active`).
