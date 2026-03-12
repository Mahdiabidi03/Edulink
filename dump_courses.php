<?php
require 'vendor/autoload.php';
use App\Kernel;
use App\Entity\Cours;

$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));
$kernel->boot();
$container = $kernel->getContainer();
$em = $container->get('doctrine.orm.entity_manager');

$courses = $em->getRepository(Cours::class)->findAll();
foreach ($courses as $c) {
    echo $c->getId() . ": " . $c->getTitle() . "\n";
}
