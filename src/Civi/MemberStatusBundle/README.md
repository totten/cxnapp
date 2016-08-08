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
 * `memberships.civicrmorg_http` (an HTTP service from `civicrm.org`)

To choose specify which source you want to use, edit `app/config/parameters.yml`
and specify `memberships_source`.
