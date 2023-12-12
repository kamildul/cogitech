<?php

namespace App\Command;

use GuzzleHttp\Client;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Post;
use App\Entity\User;

#[AsCommand(
    name: 'app:getPosts',
    description: 'Get posts',
)]
class GetPostsCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Get posts');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $client = new Client();

        $response = $client->get('https://jsonplaceholder.typicode.com/users');
        $users = json_decode($response->getBody(), true);

        $response = $client->get('https://jsonplaceholder.typicode.com/posts');
        $posts = json_decode($response->getBody(), true);
        foreach ($posts as $post) {
            $userId = $post['userId'];
            $filteredUsers = array_filter($users, function ($user) use ($userId) {
                return $user['id'] == $userId;
            });
            $userArray = array_values($filteredUsers)[0];
            $post['user'] = $userArray['name'];
            $this->savePostToDatabase($post);
        }

        $io->success('Posts have been successfully fetched and saved to the database.');
        return Command::SUCCESS;
    }

    private function savePostToDatabase(array $postData): void
    {

        $existingPost = $this->entityManager->getRepository(Post::class)->findOneBy(['id' => $postData['id']]);
        if ($existingPost) { return; }

        $postEntity = new Post(); 
        $postEntity->setTitle($postData['title']);
        $postEntity->setBody($postData['body']);
        $postEntity->setUser($postData['user']);
        $this->entityManager->persist($postEntity);
        $this->entityManager->flush();
    }
    
}
