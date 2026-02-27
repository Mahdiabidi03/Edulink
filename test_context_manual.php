<?php
use App\Kernel;
use Symfony\Component\HttpFoundation\Request;

require 'vendor/autoload.php';

$kernel = new Kernel('dev', true);
$kernel->boot();
$container = $kernel->getContainer();

$matiereRepo = $container->get('doctrine')->getRepository(\App\Entity\Matiere::class);
$coursRepo = $container->get('doctrine')->getRepository(\App\Entity\Cours::class);
$eventRepo = $container->get('doctrine')->getRepository(\App\Entity\Event::class);
$postRepo = $container->get('doctrine')->getRepository(\App\Entity\CommunityPost::class);

$contextService = new \App\Service\ContextService($matiereRepo, $coursRepo, $eventRepo, $postRepo);

$users = $container->get('doctrine')->getRepository(\App\Entity\User::class)->findAll();
foreach ($users as $user) {
    try {
        $context = $contextService->getUserContext($user);
    } catch (\Throwable $e) {
        echo "ERROR on User ID " . $user->getId() . ": " . $e->getMessage() . "\n";
    }
}
echo "Done checking all users.\n";
