# twindb
TwinDB


Configuration is much easier than you might think because there are configuration scripts in packages.
What I usually do is 
1) build rpm packages on `app`:
```make rpm
```
2) Install `-dispatcher`, `-console` packages on `app`
3) Install `-db` package on `db` servers, and then run `twindb-db_upgrade` on the master. It will create a user, schema, apply all schema updates
4) Install `-storage` package on `storage` servers. Register the storage servers with `twindb-register-storage`

There is one problem though. We deleted private keys from the repo:
support/keys/twindb-dispatcher-gpg.key
support/keys/twindb-dispatcher-ssh.key
and SSL certificate for console.twindb.com

That will break packages, we need to fix it and make the private keys auto-generated
