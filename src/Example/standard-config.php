<?php

/**
 * a standard config 标准配置
 * 下面这个例子，意在说明，有2个集群。每个集群都是一主多从（redis是不可以配置多主结构的，对于一个主，你可以做哨兵进行监控，一旦发现问题，替补上主！）。
 * 配置说明：
 * 1、配置是按集群划分的。以下面的配置为例，表示2个集群，每个集群有一个主库（写库，type为write），其余为从库，从库只能用于读
 *    数据（type为read）。当然，如果条件允许，你可以为所有从库设置一个vip，也是一个非常不错的方案
 *    如果你的某个数据库配置，既可以读，也可以写，则copy一个配置项，将write改为read即可。
 * 2、配置项说明：weight：权重。每一级权重将分配160个虚拟节点，如果为2则该节点共320个虚拟节点。auth用于设置redis的密码，type表示当前节
 *    点类型，db表示数据库号
 * 3、配置项的顺序是随意的。举例来说，第一个集群中，write是一个，如果是2个，那么哪个配置谁先谁后，不影响集群的hash分布
 * 4、权重配置：weight。每个cluster有一个权重配置，这个权重是相对cluster而言的。而每个cluster中，write组和read组的每个配置也都有weight，
 *    这个weight，是相对同组中其他的配置而言的。
 *
 */

return

    [
        [
            'write'  => [
                ['host' => 'x.x.1.1', 'port' => 6379, 'auth' => 'passwd', 'db' => 0, 'type' => 'write', 'weight' => 1],
            ],
            'read'   => [
                ['host' => 'x.x.1.2', 'port' => 6379, 'auth' => 'passwd', 'db' => 0, 'type' => 'read', 'weight' => 1],
                ['host' => 'x.x.1.3', 'port' => 6379, 'auth' => 'passwd', 'db' => 0, 'type' => 'read', 'weight' => 1],
            ],
            'weight' => 1,  //cluster的权重配置，用于做hash虚拟节点数量分配
        ],
        [
            'write'  => [
                ['host' => 'x.x.2.1', 'port' => 6379, 'auth' => 'passwd', 'db' => 0, 'type' => 'write', 'weight' => 1],
            ],
            'read'   => [
                ['host' => 'x.x.2.2', 'port' => 6379, 'auth' => 'passwd', 'db' => 0, 'type' => 'read', 'weight' => 1],
                ['host' => 'x.x.2.3', 'port' => 6379, 'auth' => 'passwd', 'db' => 0, 'type' => 'read', 'weight' => 1],
                ['host' => 'x.x.2.4', 'port' => 6379, 'auth' => 'passwd', 'db' => 0, 'type' => 'read', 'weight' => 1],
                ['host' => 'x.x.2.5', 'port' => 6379, 'auth' => 'passwd', 'db' => 0, 'type' => 'read', 'weight' => 1],
            ],
            'weight' => 1,
        ],
    ];

