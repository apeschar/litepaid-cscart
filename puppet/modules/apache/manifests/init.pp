class apache {
    package { ['apache2', 'apache2-mpm-prefork']:
        ensure => present;
    }

    service { 'apache2':
        ensure  => running,
        require => Package['apache2'];
    }

    apache::module { ['expires.load', 'rewrite.load']: }
}
