# Elasticsearch 8 for Mahara development

If you are wanting to use Elasticsearch 8.x in your development environment these instructions should get you started.  These only get the local cluster up and running.  See the Mahara documentation for configuration there.

> This README assumes you are familiar with [Docker](https://www.docker.com/).
> If the contents here do not work please check [the guide](https://www.elastic.co/guide/en/kibana/current/docker.html) for the latest version.
> All commands are run from the directory this file is in.

This is build from [here](https://www.elastic.co/guide/en/elasticsearch/reference/8.10/docker.html#docker-compose-file). These instructions have been tested with [my](https://wiki.mahara.org/wiki/User:Gold) environment and extra notes added for the issues I ran into.

## First run
### .env
Start by creating the local `.env` file. This has default values already set. Update these if you wish.

```
cp example.env .env
```

### Initial start
Start the cluster

```
docker-compose up -d
```

It can take a moment for kibana to start. You will be able to access it here when ready though: http://localhost:5601

### Create the 'mahara' user
Once Kibana has started login as the `elastic` user and create a new user for Mahara. The default password is `ThisIsThePassword`

In Kibana open *Stack Management > Security:Users* and click Create User.

For development I just created one user and gave that `user` the superuser role. For reasons that are hopefully obvious this should not be done in a production environment.

> If you will be using this cluster for Behat testing create this user with the username 'mahara' and the password 'ThisIsThePassword'.  Behat has these values hardcoded into its config at the moment.

This username/password is what is added to the `config.php` file for the following (see "Configuration for Mahara" in the next section.):

> While this is standing up ElasticSearch 8 the `elasticsearch7` search plugin appears to be able to work with it fine.

```php
$cfg->plugin_search_elasticsearch7_username
$cfg->plugin_search_elasticsearch7_password
$cfg->plugin_search_elasticsearch7_indexingusername
$cfg->plugin_search_elasticsearch7_indexingpassword
```

## General usage

### Configuration for Mahara

Add the following to your `htdocs/config.php`:
```php
$cfg->plugin_search_elasticsearch7_host = 'localhost';
$cfg->plugin_search_elasticsearch7_port = '9200';
$cfg->plugin_search_elasticsearch7_scheme = 'https';
$cfg->plugin_search_elasticsearch7_types = 'usr,interaction_instance,interaction_forum_post,group,view,artefact,block_instance,event_log';
$cfg->plugin_search_elasticsearch7_username = 'mahara';
$cfg->plugin_search_elasticsearch7_password = 'ThisIsThePassword';
$cfg->plugin_search_elasticsearch7_indexingusername = 'mahara';
$cfg->plugin_search_elasticsearch7_indexingpassword = 'ThisIsThePassword';
# This is only used if productionmode is false.
$cfg->plugin_search_elasticsearch7_ignoressl = true;
```
### Ignore SSL on non-production site
In addition to the `$cfg` above, ensure `$cfg->productionmode = false` is also set.

If `productionmode` is `true`, then the `ignoressl` setting will be ignored and the following error will be presented:

> Error: SSL certificate problem: unable to get local issuer certificate

### Stand up the cluster

This will stand up the cluster and let it run in the background returning the shell to you.

```
docker-compose up -d
```

### Stop the cluster

```
docker-compose stop
```

### Monitor logs in the cluster

This will connect to the cluster and pipe all logs to the shell.  The `-f` holds the connection open and outputs real-time logging. Log lines are prefixed with the container that the entry is from and, if your shell supports it, these are colour coded.

```
docker-compose logs -f
```

### Tear down the cluster

If you are finished with the development task you wanted the cluster for this will tear it down and clean up the containers.

```
# Clean up containers and volumes.
docker-compose  down -v
# Clean up images.
docker rmi $(docker images docker.elastic.co/elasticsearch/elasticsearch -q)
docker rmi $(docker images docker.elastic.co/kibana/kibana -q)
```

This will remove the images, containers and volumes created for this cluster freeing up the associated disc space. Standing the cluster up again after this will require you start at **First Run** again.