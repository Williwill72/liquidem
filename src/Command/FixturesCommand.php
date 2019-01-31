<?php

namespace App\Command;

use App\Entity\Message;
use App\Entity\Question;
use App\Entity\Subject;
use Doctrine\ORM\EntityManagerInterface;
use function Sodium\add;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class FixturesCommand extends Command
{
    protected static $defaultName = 'app:fixtures';
    protected $em;

    public function __construct(EntityManagerInterface $em, ?string$name = null)
    {
        $this->em = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Load dummy data in our database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->text("coucou");
        $io->text("Now loading fixtures...");

        $faker = \Faker\Factory::create('fr_FR');

        $conn = $this->em->getConnection();

        $conn->query('SET FOREIGN_KEY_CHECKS = 0');
        $conn->query('TRUNCATE question');
        $conn->query('TRUNCATE message');
        $conn->query('TRUNCATE subject');
        $conn->query('TRUNCATE question_subject');
        $conn->query('SET FOREIGN_KEY_CHECKS = 1');

        $subjects = ["Affaires étrangères","Affaires européennes","Agriculture,,","Ruralité","Aménagement du territoire","Économie et finance","Culture","Communication","Défense","Écologie et développement durable","Transports","Logement","Éducation","Intérieur","Outre-mer et collectivités territoriales","Immigration","Justice et Libertés","Travail","Santé","Démocratie"];
        $subjectsEntity = [];

        foreach ($subjects as $label){
            $subject = new Subject();
            $subject->setLabel($label);
            $this->em->persist($subject);
            $subjectsEntity[] = $subject;
        }

        $this->em->flush();

        for($i=0;$i<100;$i++){

            $question = new Question();
            $question->setTitle($faker->name);
            $question->setDescription($faker->realText(5000));
            $question->setStatus($faker->randomElement(['debating', 'voting', 'closed']));
            $question->setCreationDate($faker->dateTimeBetween('-1 year', 'now'));
            $question->setSupports($faker->optional(0.5, 0)->numberBetween(0, 47000000));

            $num = mt_rand(1,3);
            for($x=0;$x<$num;$x++) {
                $s = $faker->randomElement($subjectsEntity);
                $question->addSubject($s);
            }

            for($j=0;$j<5;$j++){

                $message = new Message();
                $message->setContent($faker->realText(200));
                $message->setCreationDate($faker->dateTimeBetween($question->getCreationDate(), 'now'));
                $message->setIsPublished($faker->boolean(80));
                $message->setClaps($faker->numberBetween(0,1000));
                $message->setQuestion($question);

                $this->em->persist($message);
            }

            $this->em->persist($question);
        }

        $this->em->flush();

        $io->success('!done');
    }
}
