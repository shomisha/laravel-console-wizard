<?php

namespace Shomisha\LaravelConsoleWizard\Test\Unit;

use Illuminate\Support\Collection;
use Shomisha\LaravelConsoleWizard\Questions\ChoiceQuestion;
use Shomisha\LaravelConsoleWizard\Questions\TextQuestion;
use Shomisha\LaravelConsoleWizard\Questions\UniqueMultipleChoiceQuestion;
use Shomisha\LaravelConsoleWizard\Test\TestCase;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\BaseTestWizard;

class WizardTest extends TestCase
{
    /** @var \ReflectionProperty */
    protected $questions;

    /** @var \ReflectionProperty */
    protected $asked;

    /** @var \ReflectionProperty */
    protected $answers;

    /**
     * @param string $wizardClass
     * @return \Shomisha\LaravelConsoleWizard\Command\Wizard
     */
    protected function loadWizard(string $wizardClass)
    {
        $this->questions = tap(new \ReflectionProperty($wizardClass, 'questions'))
             ->setAccessible(true);

        $this->asked = tap(new \ReflectionProperty($wizardClass, 'asked'))
             ->setAccessible(true);

        $this->answers = tap(new \ReflectionProperty($wizardClass, 'answers'))
             ->setAccessible(true);

        $wizard = $this->app->get($wizardClass);

        $this->app->instance($wizardClass, $wizard);

        return $wizard;
    }

    /** @test */
    public function wizard_will_initialize_questions_when_created()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);
        $questions = $this->questions->getValue($wizard);

        $this->assertInstanceOf(TextQuestion::class, $questions->get('name'));
        $this->assertInstanceOf(TextQuestion::class, $questions->get('age'));
        $this->assertInstanceOf(ChoiceQuestion::class, $questions->get('preferred-language'));
    }

    /** @test */
    public function wizard_will_initialize_an_empty_collection_for_asked_questions()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);
        $asked = $this->asked->getValue($wizard);

        $this->assertInstanceOf(Collection::class, $asked);
        $this->assertEmpty($asked);
    }

    /** @test */
    public function wizard_will_initialize_an_empty_collection_for_answers()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);
        $answers = $this->answers->getValue($wizard);

        $this->assertInstanceOf(Collection::class, $answers);
        $this->assertEmpty($answers);
    }

    /** @test */
    public function wizard_will_ask_all_the_defined_questions()
    {
        $this->artisan('console-wizard-test:base')
             ->expectsQuestion("What's your name?", 'Misa')
             ->expectsQuestion("How old are you?", 25)
             ->expectsQuestion("Your favourite programming language", 0)
             ->run();
    }

    /** @test */
    public function wizard_will_store_all_the_answers()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion("What's your name?", 'Misa')
             ->expectsQuestion("How old are you?", 25)
             ->expectsQuestion("Your favourite programming language", 'PHP')
             ->run();

        $answers = $this->answers->getValue($wizard);

        $this->assertEquals([
            'name' => 'Misa',
            'age' => 25,
            'preferred-language' => 'PHP',
        ], $answers->toArray());
    }

    /** @test */
    public function wizard_will_move_asked_questions_to_the_asked_collection()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion("What's your name?", 'Misa')
             ->expectsQuestion("How old are you?", 25)
             ->expectsQuestion("Your favourite programming language", 0)
             ->run();

        $asked = $this->asked->getValue($wizard);
        $questions = $this->questions->getValue($wizard);

        $this->assertInstanceOf(TextQuestion::class, $asked->get('name'));
        $this->assertInstanceOf(TextQuestion::class, $asked->get('age'));
        $this->assertInstanceOf(ChoiceQuestion::class, $asked->get('preferred-language'));

        $this->assertInstanceOf(Collection::class, $questions);
        $this->assertEmpty($questions);
    }
}