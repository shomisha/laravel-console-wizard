<?php

namespace Shomisha\LaravelConsoleWizard\Test\Unit;

use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Shomisha\LaravelConsoleWizard\Exception\InvalidStepException;
use Shomisha\LaravelConsoleWizard\Steps\ChoiceStep;
use Shomisha\LaravelConsoleWizard\Steps\ConfirmStep;
use Shomisha\LaravelConsoleWizard\Steps\OneTimeWizard;
use Shomisha\LaravelConsoleWizard\Steps\RepeatStep;
use Shomisha\LaravelConsoleWizard\Steps\TextStep;
use Shomisha\LaravelConsoleWizard\Test\TestCase;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\BaseTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\StepValidationTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\SubwizardTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\WizardValidationTestWizard;
use Shomisha\LaravelConsoleWizard\Test\TestWizards\WizardWithOneTimeSubwizard;

class WizardTest extends TestCase
{
    /** @var \ReflectionProperty */
    protected $steps;

    /** @var \ReflectionProperty */
    protected $taken;

    /** @var \ReflectionProperty */
    protected $followup;

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

        $this->followup = tap(new \ReflectionProperty($wizardClass, 'followup'))
             ->setAccessible(true);

        $wizard = $this->app->get($wizardClass);

        $wizard->initializeWizard();

        $this->app->instance($wizardClass, $wizard);

        return $wizard;
    }

    protected function partiallyMockWizard(string $class, array $methods)
    {
        $mock = \Mockery::mock(sprintf(
            '%s[%s]',
            $class,
            implode(',', $methods)
        ));

        $this->instance($class, $mock);

        return $mock;
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
    public function wizard_will_initialize_steps_when_created()
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
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);
        $mock->shouldReceive('getSteps')->once()->andReturn([
            new TextStep("What's your name?"),
            new InvalidStepException(),
        ]);

        $this->expectException(InvalidStepException::class);

        $this->artisan('console-wizard-test:base');
    }

    /** @test */
    public function wizard_will_perform_no_actions_prior_to_asserting_all_steps_are_valid()
    {
        $this->expectException(InvalidStepException::class);

        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps', 'take', 'completed']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            new TextStep("What's your name?"),
            new InvalidStepException(),
        ]);

        $mock->shouldNotReceive('take');
        $mock->shouldNotReceive('completed');

        $this->artisan('console-wizard-test:base');
    }

    /** @test */
    public function wizard_will_initialize_an_empty_collection_for_taken_steps()
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
    public function wizard_will_initialize_an_empty_collection_for_followups()
    {
        $wizard = $this->loadWizard(BaseTestWizard::class);
        $followup = $this->followup->getValue($wizard);

        $this->assertInstanceOf(Collection::class, $followup);
        $this->assertEmpty($followup);
    }

    /** @test */
    public function wizard_will_ask_all_the_defined_steps()
    {
        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_will_ask_all_the_steps_from_a_subwizard()
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
    public function wizard_can_use_one_time_wizard_as_subwizard()
    {
        $wizard = $this->loadWizard(WizardWithOneTimeSubwizard::class);

        $this->artisan('console-wizard-test:one-time-subwizard')
            ->expectsQuestion('Answer the first step', 'First answer')
            ->expectsQuestion('Answer the second step', 'Second answer');

        $answers = $this->answers->getValue($wizard);
        $this->assertEquals([
            'one-time-subwizard' => [
                'first-question' => 'First answer',
                'second-question' => 'Second answer',
            ]
        ], $answers->toArray());
    }

    /** @test */
    public function one_time_wizards_completed_method_cannot_be_invoked()
    {
        $this->expectException(\RuntimeException::class);

        $wizard = new OneTimeWizard([]);

        $wizard->completed();
    }

    /** @test */
    public function wizard_can_validate_answers_on_a_per_step_basis()
    {
        $mock = $this->partiallyMockWizard(StepValidationTestWizard::class, ['onInvalidAge', 'onInvalidFavouriteColour']);
        $invalidAgeHandlerExpectation = $mock->shouldReceive('onInvalidAge');
        $invalidColourHandlerExpectation = $mock->shouldReceive('onInvalidFavouriteColour');


        $this->artisan('console-wizard-test:step-validation')
            ->expectsQuestion('What is your name?', 'Misa')
            ->expectsQuestion('How old are you?', 13)
            ->expectsQuestion('What is your favourite colour?', 'red');


        $invalidAgeHandlerExpectation->verify();
        $invalidColourHandlerExpectation->verify();
    }

    /** @test */
    public function wizard_will_throw_a_validation_exception_if_a_validation_handler_is_missing()
    {
        $mock = $this->partiallyMockWizard(StepValidationTestWizard::class, ['onInvalidAge']);
        $mock->shouldReceive('onInvalidAge');
        $this->expectException(ValidationException::class);

        $this->artisan('console-wizard-test:step-validation')
            ->expectsQuestion('What is your name?', 'Misa')
            ->expectsQuestion('How old are you?', 13)
            ->expectsQuestion('What is your favourite colour?', 'magenta');
    }

    /** @test */
    public function wizard_can_validate_answers_on_a_complete_wizard_basis()
    {
        $mock = $this->partiallyMockWizard(WizardValidationTestWizard::class, ['onWizardInvalid']);
        $expectation = $mock->shouldReceive('onWizardInvalid')->once();

        $this->artisan('console-wizard-test:wizard-validation')
            ->expectsQuestion("What is your name?", 'Misa')
            ->expectsQuestion("What is your favourite band?", 'Invalid answer')
            ->expectsQuestion("Which country do you come from?", "Another invalid answer");

        $expectation->verify();
    }

    /** @test */
    public function wizard_will_invoke_existing_taking_modifiers()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['takingName', 'takingAge']);
        $mock->shouldReceive('takingName')->once();
        $mock->shouldReceive('takingAge')->once();

        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_will_not_invoke_non_existing_taking_modifiers()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['takingPreferredLanguage']);
        $mock->shouldNotReceive('takingPreferredLanguage');

        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_will_invoke_existing_answered_modifiers()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['answeredAge', 'answeredPreferredLanguage']);
        $mock->shouldReceive('answeredAge')->once();
        $mock->shouldReceive('answeredPreferredLanguage')->once();

        $this->runBaseTestWizard();
    }

    /** @test */
    public function answered_modifier_results_will_be_saved_as_answers()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['answeredAge', 'answeredPreferredLanguage']);
        $mock->shouldReceive('answeredAge')->once()->andReturn('modified age');
        $mock->shouldReceive('answeredPreferredLanguage')->once()->andReturn('modified programming language');


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
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['answeredName']);
        $mock->shouldNotReceive('answeredName');

        $this->runBaseTestWizard();
    }

    /** @test */
    public function wizard_can_followup_on_steps()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'run-another' => new TextStep('Should I followup on this step?'),
            'i-ran-another' => new TextStep('Is it OK I ran another step?'),
        ]);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion('Should I followup on this step?', 'Yes')
             ->expectsQuestion("I am a followup.", 'Cool')
             ->expectsQuestion('Is it OK I ran another step?', 'Totally');
    }

    /** @test */
    public function followups_will_be_asked_from_the_latest_to_the_earliest()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'main-step' => new TextStep("I am the main step"),
        ]);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion('I am the main step', 'Cool')
             ->expectsQuestion('I am added after the main step', 'Yes, you are')
             ->expectsQuestion('I am added before the main step', 'Good for you');
    }

    /** @test */
    public function wizard_can_repeat_steps()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'repeat_me' => new TextStep("I should be repeated"),
            'repeat-after-me' => new ConfirmStep("Should I repeat him, though?"),
        ]);


        $this->artisan('console-wizard-test:base')
            ->expectsQuestion("I should be repeated", "Indeed you should")
            ->expectsConfirmation("Should I repeat him, though?", 'yes')
            ->expectsQuestion("I should be repeated", "And repeated you are.");
    }

    /** @test */
    public function wizard_can_only_repeat_taken_or_skipped_tests()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'repeat-after-me' => new TextStep("I really want to repeat a step"),
            'second-step' => new TextStep("I'm just chilling here"),
            'repeat_me' => new TextStep("Y U NO REPEAT ME"),
        ]);


        $this->artisan('console-wizard-test:base')
            ->expectsQuestion("I really want to repeat a step", "But you cannot")
            ->expectsQuestion("I'm just chilling here", "Good for you")
            ->expectsQuestion("Y U NO REPEAT ME", "Because you're too late");
    }

    /** @test */
    public function wizard_can_skip_steps()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);
        $mock->shouldReceive('getSteps')->once()->andReturn([
            'unskippable' => new TextStep("I shouldn't be skipped"),
            'skip-me'     => new TextStep("I am to be skipped"),
            'i-will-run'  => new TextStep("Running"),
        ]);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion("I shouldn't be skipped", "That's right")
             ->expectsQuestion('Running', 'good for you');
    }

    /** @test */
    public function wizard_cannot_skip_a_step_that_is_already_running()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);
        $mock->shouldReceive('getSteps')->once()->andReturn([
            'i-will-run'  => new TextStep('Running'),
            'unskippable' => new TextStep("I'm running too"),
        ]);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion('Running', 'Good for you')
             ->expectsQuestion("I'm running too", 'Yes you are');
    }

    /** @test */
    public function wizard_can_repeat_a_step_a_fixed_number_of_times()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'repeated' => tap(new RepeatStep(new TextStep("I will run three times")))->times(3),
        ]);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion('I will run three times', "Yes you will")
             ->expectsQuestion('I will run three times', "That's right")
             ->expectsQuestion('I will run three times', "Okay, we get it");
    }

    /** @test */
    public function wizard_can_repeat_a_step_until_a_specified_answer_is_provided()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            tap(new RepeatStep(new TextStep("Gimme 5 or I shall never stop")))->untilAnswerIs(5),
        ]);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion("Gimme 5 or I shall never stop", 3)
             ->expectsQuestion("Gimme 5 or I shall never stop", 2)
             ->expectsQuestion("Gimme 5 or I shall never stop", 7)
             ->expectsQuestion("Gimme 5 or I shall never stop", "You can't have 5")
             ->expectsQuestion("Gimme 5 or I shall never stop", 5);
    }

    public function callbackRepetitionTests()
    {
        return [
            [
                function($answer) {
                    return $answer === 'stop';
                }, ['go on', 'keep running', 'continue', 'stop'],
            ],
            [
                function($answer) {
                    if ($answer === null) {
                        return false;
                    }

                    return $answer > 20;
                }, [1, 7, 4, 12, 19, 55],
            ],
            [
                function($answer) {
                    if ($answer === null) {
                        return false;
                    }

                    return !is_string($answer);
                }, ['go on', 'keep it up', 'this is the last time', false],
            ]
        ];
    }

    /**
     * @test
     * @dataProvider callbackRepetitionTests
     */
    public function wizard_can_repeat_a_step_until_a_specific_condition_is_met(callable $callback, array $promptAnswers)
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'repeated' => tap(new RepeatStep(new TextStep("Repeat me")))->until($callback),
        ]);

        $consoleExpectation = $this->artisan('console-wizard-test:base');

        foreach ($promptAnswers as $answer) {
            $consoleExpectation->expectsQuestion("Repeat me", $answer);
        }
    }

    /** @test */
    public function wizard_can_repeat_a_steps_as_long_as_the_user_requests_so()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->andReturn([
            'repeated' => tap(new RepeatStep(new TextStep('Repeat me')))->withRepeatPrompt('Repeat me again?'),
        ]);


        $this->artisan('console-wizard-test:base')
            ->expectsQuestion('Repeat me', 'I will')
            ->expectsConfirmation('Repeat me again?', 'yes')
            ->expectsQuestion('Repeat me', 'Okay')
            ->expectsConfirmation('Repeat me again?', 'yes')
            ->expectsQuestion('Repeat me', 'Once more')
            ->expectsConfirmation('Repeat me again?', 'yes')
            ->expectsQuestion('Repeat me', 'No more')
            ->expectsConfirmation('Repeat me again?', 'no');

        $this->assertEquals([
            'I will', 'Okay', 'Once more', 'No more'
        ], $mock->getAnswers()->get('repeated'));
    }

    /** @test */
    public function wizard_can_prompt_the_user_before_the_first_repetition()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->andReturn([
            'repeated' => tap(new RepeatStep(new TextStep('Repeat me')))->withRepeatPrompt('Repeat me again?', true),
        ]);


        $this->artisan('console-wizard-test:base')
             ->expectsConfirmation('Repeat me again?', 'yes')
             ->expectsQuestion('Repeat me', 'I will')
             ->expectsConfirmation('Repeat me again?', 'yes')
             ->expectsQuestion('Repeat me', 'Okay')
             ->expectsConfirmation('Repeat me again?', 'yes')
             ->expectsQuestion('Repeat me', 'Once more')
             ->expectsConfirmation('Repeat me again?', 'yes')
             ->expectsQuestion('Repeat me', 'No more')
             ->expectsConfirmation('Repeat me again?', 'no');

        $this->assertEquals([
            'I will', 'Okay', 'Once more', 'No more'
        ], $mock->getAnswers()->get('repeated'));
    }

    /** @test */
    public function repeated_step_answers_will_be_returned_as_an_array()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'text-step' => new TextStep("My answer will be a string"),
            'repeated'  => tap(new RepeatStep(new TextStep("My answer will be an array with 3 elements")))->times(3),
        ]);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion("My answer will be a string", "Yes it will")
             ->expectsQuestion("My answer will be an array with 3 elements", "True that")
             ->expectsQuestion("My answer will be an array with 3 elements", "Here's the second element")
             ->expectsQuestion("My answer will be an array with 3 elements", "And I'm the third");

        $answers = tap(new \ReflectionProperty(get_class($mock), 'answers'))->setAccessible(true)->getValue($mock);
        $this->assertEquals([
            'text-step' => 'Yes it will',
            'repeated' => [
                'True that',
                "Here's the second element",
                "And I'm the third",
            ]
        ], $answers->toArray());
    }

    /** @test */
    public function repeated_step_can_exclude_the_last_answer()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'repeated' => tap(new RepeatStep(new TextStep("Gimme 5 or I shall never stop")))->untilAnswerIs(5)->withoutLastAnswer(),
        ]);

        $this->artisan('console-wizard-test:base')
             ->expectsQuestion("Gimme 5 or I shall never stop", 3)
             ->expectsQuestion("Gimme 5 or I shall never stop", 2)
             ->expectsQuestion("Gimme 5 or I shall never stop", 7)
             ->expectsQuestion("Gimme 5 or I shall never stop", "You can't have 5")
             ->expectsQuestion("Gimme 5 or I shall never stop", 5);

        $answers = $mock->getAnswers();
        $this->assertEquals([
            3, 2, 7, "You can't have 5",
        ], $answers->get('repeated'));
    }

    /** @test */
    public function wizard_will_throw_an_exception_if_a_repeated_question_is_not_properly_initialized()
    {
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['getSteps']);

        $mock->shouldReceive('getSteps')->once()->andReturn([
            'repeated' => new RepeatStep(new TextStep("I'll never be ran")),
        ]);

        $this->expectException(InvalidStepException::class);

        $this->artisan('console-wizard-test:base');
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
    public function wizard_will_move_taken_steps_to_the_taken_collection()
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
        $mock = $this->partiallyMockWizard(BaseTestWizard::class, ['completed']);
        $mock->shouldNotReceive('completed')->once();

        $this->runBaseTestWizard();
    }
}
