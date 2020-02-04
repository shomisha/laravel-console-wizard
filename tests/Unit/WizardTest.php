<?php

namespace Shomisha\LaravelConsoleWizard\Test\Unit;

use Illuminate\Support\Collection;
use Shomisha\LaravelConsoleWizard\Exception\InvalidStepException;
use Shomisha\LaravelConsoleWizard\Steps\ChoiceStep;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;
use Shomisha\LaravelConsoleWizard\Test\TestCase;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\BaseTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\InvalidStepsTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\SubwizardTestWizard;

class WizardTest extends TestCase
{
    /** @var \ReflectionProperty */
    protected $steps;

    /** @var \ReflectionProperty */
    protected $taken;

    /** @var \ReflectionProperty */
    protected $answers;

    /**
     * @param string $wizardClass
     * @return \Shomisha\LaravelConsoleWizard\Command\Wizard
     */
    protected function loadWizard(string $wizardClass)
    {
        $this->steps = tap(new \ReflectionProperty($wizardClass, 'steps'))
             ->setAccessible(true);

        $this->taken = tap(new \ReflectionProperty($wizardClass, 'taken'))
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
        $steps = $this->steps->getValue($wizard);

        $this->assertInstanceOf(TextStep::class, $steps->get('name'));
        $this->assertInstanceOf(TextStep::class, $steps->get('age'));
        $this->assertInstanceOf(ChoiceStep::class, $steps->get('preferred-language'));
    }

    /** @test */
    public function wizard_will_throw_an_exception_if_an_invalid_step_is_expected()
    {
        $this->expectException(InvalidStepException::class);

        $this->loadWizard(InvalidStepsTestWizard::class);
    }

    /** @test */
    public function wizard_will_perform_no_actions_prior_to_asserting_all_steps_are_valid()
    {
        $this->expectException(InvalidStepException::class);

        $mock = \Mockery::mock(sprintf("%s[handle, take, completed]", InvalidStepsTestWizard::class));

        $mock->shouldNotHaveReceived('handle');
        $mock->shouldNotHaveReceived('take');
        $mock->shouldNotHaveReceived('completed');
    }

    /** @test */
    public function wizard_will_initialize_an_empty_collection_for_asked_questions()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);
        $taken = $this->taken->getValue($wizard);

        $this->assertInstanceOf(Collection::class, $taken);
        $this->assertEmpty($taken);
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
    public function wizard_will_ask_all_the_questions_from_a_subwizard()
    {
        $this->artisan('console-wizard-test:subwizard')
             ->expectsQuestion("What's your name?", 'Misa')
             ->expectsQuestion("Are you older than 18?", "Yes")
             ->expectsQuestion("Your marital status:", 'single');
    }

    /** @test */
    public function subwizard_answers_will_be_present_as_a_subset_of_main_wizard_answers()
    {
        $wizard = $this->loadWizard(SubwizardTestWizard::class);

        $this->artisan('console-wizard-test:subwizard')
             ->expectsQuestion("What's your name?", 'Misa')
             ->expectsQuestion("Are you older than 18?", "Yes")
             ->expectsQuestion("Your marital status:", 'single');

        $answers = $this->answers->getValue($wizard);
        $this->assertEquals([
            'name' => 'Misa',
            'legal-status' => [
                'age-legality' => 'Yes',
                'marital-legality' => 'single',
            ],
        ], $answers->toArray());
    }

    /** @test */
    public function wizard_will_invoke_existing_taking_modifiers()
    {
        $mock = \Mockery::mock(sprintf('%s[takingName, takingAge]', BaseTestWizard::class));
        $mock->shouldReceive('takingName')->once();
        $mock->shouldReceive('takingAge')->once();

        $this->instance(BaseTestWizard::class, $mock);

        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_will_not_invoke_non_existing_taking_modifiers()
    {
        $mock = \Mockery::mock(sprintf('%s[takingPreferredLanguage]', BaseTestWizard::class));
        $mock->shouldNotReceive('takingPreferredLanguage');

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

        $asked = $this->taken->getValue($wizard);
        $questions = $this->steps->getValue($wizard);

        $this->assertInstanceOf(TextStep::class, $asked->get('name'));
        $this->assertInstanceOf(TextStep::class, $asked->get('age'));
        $this->assertInstanceOf(ChoiceStep::class, $asked->get('preferred-language'));

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