<?php

namespace Shomisha\LaravelConsoleWizard\Test\Unit;


use Tests\TestCase;

class GenerateWizardWizardTest extends TestCase
{
    protected function path(string $filename = ''): string
    {
        return base_path("/storage/framework/test/GenerateWizardWizardTest/{$filename}");
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->useAppPath($this->path());
    }

    protected function tearDown(): void
    {
        $this->app['files']->deleteDirectory($this->path());

        parent::tearDown();
    }

    /** @test */
    public function command_can_generate_wizard()
    {
        $this->artisan('wizard:generate')
             ->expectsQuestion("Enter the class name for your wizard", 'TestWizard')
             ->expectsQuestion("Enter the signature for your wizard", "wizard:test")
             ->expectsQuestion('Enter the description for your wizard', 'This is a test wizard.')
             ->expectsConfirmation("Do you want to add a wizard step?", 'no');


        $generatedWizard = $this->app['files']->get($this->path('Console/Command/TestWizard.php'));
        $this->assertIsString($generatedWizard);

        $this->assertStringContainsString('namespace App\Console\Command;', $generatedWizard);
        $this->assertStringContainsString('use Shomisha\LaravelConsoleWizard\Command\Wizard;', $generatedWizard);
        $this->assertStringContainsString('class TestWizard extends Wizard', $generatedWizard);

        $this->assertStringContainsString("protected \$signature = 'wizard:test';", $generatedWizard);
        $this->assertStringContainsString("protected \$description = 'This is a test wizard.';", $generatedWizard);

        $this->assertStringContainsString("public function getSteps() : array\n    {\n        return [];\n    }", $generatedWizard);
        $this->assertStringContainsString("public function completed()\n    {\n        return \$this->answers->all();\n    }", $generatedWizard);
    }

    /** @test */
    public function command_can_generate_wizard_with_steps()
    {
        $stepTypeChoices = [
            0 => 'Text step',
            1 => 'Multiple answer text step',
            2 => 'Choice step',
            3 => 'Multiple choice step',
            4 => 'Unique multiple choice step',
            5 => 'Confirm step',
        ];


        $this->artisan('wizard:generate')
            ->expectsQuestion('Enter the class name for your wizard', 'TestWizardWithSteps')
            ->expectsQuestion('Enter the signature for your wizard', 'wizard:test-with-steps')
            ->expectsQuestion('Enter the description for your wizard', 'This is a test wizard with steps.')

            ->expectsConfirmation('Do you want to add a wizard step?', 'yes')
            ->expectsQuestion('Enter step name', 'first-step')
            ->expectsQuestion('Enter step question', 'First question')
            ->expectsChoice('Choose step type', 'Text step', $stepTypeChoices)
            ->expectsConfirmation("Do you want a 'taking' modifier method for this step?", 'yes')
            ->expectsConfirmation("Do you want an 'answered' modifier method for this step?", 'no')

            ->expectsConfirmation('Do you want to add a wizard step?', 'yes')
            ->expectsQuestion('Enter step name', 'second-step')
            ->expectsQuestion('Enter step question', 'Second question')
            ->expectsChoice('Choose step type', 'Choice step', $stepTypeChoices)
            ->expectsQuestion("Add option for multiple choice (enter 'stop' to stop)", 'First option')
            ->expectsQuestion("Add option for multiple choice (enter 'stop' to stop)", 'Second option')
            ->expectsQuestion("Add option for multiple choice (enter 'stop' to stop)", 'Third option')
            ->expectsQuestion("Add option for multiple choice (enter 'stop' to stop)", 'stop')
            ->expectsConfirmation("Do you want a 'taking' modifier method for this step?", 'yes')
            ->expectsConfirmation("Do you want an 'answered' modifier method for this step?", 'yes')

            ->expectsConfirmation('Do you want to add a wizard step?', 'no');


        $generatedWizard = $this->app['files']->get($this->path('Console/Command/TestWizardWithSteps.php'));
        $this->assertIsString($generatedWizard);

        $this->assertStringContainsString('namespace App\Console\Command;', $generatedWizard);

        $this->assertStringContainsString('use Shomisha\LaravelConsoleWizard\Command\Wizard;', $generatedWizard);
        $this->assertStringContainsString('use Shomisha\LaravelConsoleWizard\Steps\TextStep', $generatedWizard);
        $this->assertStringContainsString('use Shomisha\LaravelConsoleWizard\Steps\ChoiceStep', $generatedWizard);

        $this->assertStringContainsString('class TestWizardWithSteps extends Wizard', $generatedWizard);

        $this->assertStringContainsString("protected \$signature = 'wizard:test-with-steps';", $generatedWizard);
        $this->assertStringContainsString("protected \$description = 'This is a test wizard with steps.';", $generatedWizard);

        $this->assertStringContainsString(
            "public function getSteps() : array\n    {\n        return ['first-step' => new TextStep('First question'), 'second-step' => new ChoiceStep('Second question', ['First option', 'Second option', 'Third option'])];\n    }",
            $generatedWizard
        );
        $this->assertStringContainsString("public function takingFirstStep(Step \$step)\n    {\n    }", $generatedWizard);
        $this->assertStringContainsString("public function takingSecondStep(Step \$step)\n    {\n    }", $generatedWizard);
        $this->assertStringContainsString("public function answeredSecondStep(Step \$step, \$secondStep)\n    {\n        return \$secondStep;\n    }", $generatedWizard);

        $this->assertStringContainsString("public function completed()\n    {\n        return \$this->answers->all();\n    }", $generatedWizard);
    }
}
