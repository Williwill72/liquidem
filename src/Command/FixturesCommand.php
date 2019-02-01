<?php

namespace App\Command;

use App\Entity\Message;
use App\Entity\Question;
use App\Entity\Subject;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use function Sodium\add;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class FixturesCommand extends Command
{
    protected static $defaultName = 'app:fixtures';
    protected $em;
    protected $encoder;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $encoder,
        ?string$name = null)
    {
        $this->em = $em;
        $this->encoder = $encoder;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Load dummy data in our database');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $faker = \Faker\Factory::create('fr_FR');

        $answer = $io->ask("Truncating all tables... Sure ? [yes/no], no");
        if($answer !== "yes"){
            $io->text("aborttttttting");
            die();
        }

        $conn = $this->em->getConnection();

        $conn->query('SET FOREIGN_KEY_CHECKS = 0');

        $conn->query('TRUNCATE question');
        $conn->query('TRUNCATE message');
        $conn->query('TRUNCATE subject');
        $conn->query('TRUNCATE question_subject');
        $conn->query('TRUNCATE user');

        $conn->query('SET FOREIGN_KEY_CHECKS = 1');

        $io->text("Tables are now empty...");

        $subjects = ["Affaires étrangères","Affaires européennes","Agriculture","Ruralité","Aménagement du territoire","Économie et finance","Culture","Communication","Défense","Écologie et développement durable","Transports","Logement","Éducation","Intérieur","Outre-mer et collectivités territoriales","Immigration","Justice et Libertés","Travail","Santé","Démocratie"];
        $subjectsEntity = [];

        foreach ($subjects as $label){
            $subject = new Subject();
            $subject->setLabel($label);
            $this->em->persist($subject);
            $subjectsEntity[] = $subject;
        }

        $this->em->flush();

        $usesrs = [];

        for($n=0;$n<20;$n++) {
            $user = new User();
            $user->setUsername($faker->unique()->name);
            $user->setSocialSecurityNumber($faker->numerify('###############'));
            $user->setRoles($faker->randomElements([['admin'], ['user']]));
            $user->setEmail($faker->email);

            $password = $user->getUsername();
            $hash = $this->encoder->encodePassword($user, $password);
            $user->setPassword($hash);

            $this->em->persist($user);

            $users[] = $user;
        }

        $this->em->flush();

        //Démarre la barre de progression avc 200 étapes
        $io->progressStart(200);

        for($i=0;$i<200;$i++){

            //Fais avancer la barre de progression de 1 étape
            $io->progressAdvance(1);

            $question = new Question();
            $question->setTitle($faker->name);
            $question->setDescription($faker->realText(5000));
            $question->setStatus($faker->randomElement(['debating', 'voting', 'closed']));
            $question->setCreationDate($faker->dateTimeBetween('-1 year', 'now'));
            $question->setSupports($faker->optional(0.5, 0)->numberBetween(0, 47000000));
            $question->setUser($faker->randomElement($users));

            $num = mt_rand(1,3);
            for($x=0;$x<$num;$x++) {
                $s = $faker->randomElement($subjectsEntity);
                $question->addSubject($s);
            }

            for($j=0;$j<20;$j++){

                $message = new Message();
                $message->setContent($faker->realText(200));
                $message->setCreationDate($faker->dateTimeBetween($question->getCreationDate(), 'now'));
                $message->setIsPublished($faker->boolean(80));
                $message->setClaps($faker->numberBetween(0,1000));
                $message->setQuestion($question);
                $message->setUser($faker->randomElement($users));

                $this->em->persist($message);
            }

            $this->em->persist($question);
        }

        //Termine la barre de progression
        $io->progressFinish();

        $this->em->flush();

        $io->success('!done');
    }
}
