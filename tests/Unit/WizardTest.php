<?php

namespace Shomisha\LaravelConsoleWizard\Test\Unit;

use Illuminate\Support\Collection;
use Shomisha\LaravelConsoleWizard\Questions\ChoiceQuestion;
use Shomisha\LaravelConsoleWizard\Questions\TextQuestion;
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

    protected function runBaseTestWizard()
    {
        $this->artisan('console-wizard-test:base')
             ->expectsQuestion("What's your name?", 'Misa')
             ->expectsQuestion("How old are you?", 25)
             ->expectsQuestion("Your favourite programming language", 0)
             ->run();
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
        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_will_invoke_existing_asking_modifiers()
    {
        $mock = \Mockery::mock(sprintf('%s[askingName, askingAge]', BaseTestWizard::class));
        $mock->shouldReceive('askingName')->once();
        $mock->shouldReceive('askingAge')->once();

        $this->instance(BaseTestWizard::class, $mock);

        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_will_not_invoke_non_existing_asking_modifiers()
    {
        $mock = \Mockery::mock(sprintf('%s[askingPreferredLanguage]', BaseTestWizard::class));
        $mock->shouldNotReceive('askingPreferredLanguage');

        $this->instance(BaseTestWizard::class, $mock);

        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_will_invoke_existing_answered_modifiers()
    {
        $mock = \Mockery::mock(sprintf('%s[answeredAge, answeredPreferredLanguage]', BaseTestWizard::class));
        $mock->shouldReceive('answeredAge')->once();
        $mock->shouldReceive('answeredPreferredLanguage')->once();

        $this->instance(BaseTestWizard::class, $mock);

        $this->runBaseTestWizard();
    }

    /** @test */
    public function answered_modifier_results_will_be_saved_as_answers()
    {
        $mock = \Mockery::mock(sprintf('%s[answeredAge, answeredPreferredLanguage]', BaseTestWizard::class));
        $mock->shouldReceive('answeredAge')->once()->andReturn('modified age');
        $mock->shouldReceive('answeredPreferredLanguage')->once()->andReturn('modified programming language');

        $this->instance(BaseTestWizard::class, $mock);


        $this->runBaseTestWizard();

        $answers = tap(new \ReflectionProperty(BaseTestWizard::class, 'answers'))
            ->setAccessible(true)
            ->getValue($mock);

        $this->assertEquals('modified age', $answers->get('age'));
        $this->assertEquals('modified programming language', $answers->get('preferred-language'));
    }

    /** @test */
    public function wizard_will_not_invoke_non_existing_answered_modifiers()
    {
        $mock = \Mockery::mock(sprintf('%s[answeredName]', BaseTestWizard::class));
        $mock->shouldNotReceive('answeredName');

        $this->instance(BaseTestWizard::class, $mock);

        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_will_store_all_the_answers()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);

        $this->runBaseTestWizard();

        $answers = $this->answers->getValue($wizard);
        $this->assertEquals([
            'name' => 'Misa',
            'age' => 25,
            'preferred-language' => 0,
        ], $answers->toArray());
    }

    /** @test */
    public function wizard_will_move_asked_questions_to_the_asked_collection()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);

        $this->runBaseTestWizard();

        $asked = $this->asked->getValue($wizard);
        $questions = $this->questions->getValue($wizard);

        $this->assertInstanceOf(TextQuestion::class, $asked->get('name'));
        $this->assertInstanceOf(TextQuestion::class, $asked->get('age'));
        $this->assertInstanceOf(ChoiceQuestion::class, $asked->get('preferred-language'));

        $this->assertInstanceOf(Collection::class, $questions);
        $this->assertEmpty($questions);
    }

    /** @test */
    public function wizard_will_invoke_completed_upon_completion()
    {
        $mock = \Mockery::mock(sprintf('%s[completed]', BaseTestWizard::class));
        $mock->shouldNotReceive('completed')->once();

        $this->instance(BaseTestWizard::class, $mock);

        $this->runBaseTestWizard();
    }
}