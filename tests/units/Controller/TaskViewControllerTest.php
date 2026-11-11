<?php

namespace KanboardTests\units\Controller;

use Kanboard\Controller\TaskViewController;
use Kanboard\Core\Security\Role;
use KanboardTests\units\Base;

class TaskViewControllerTest extends Base
{
    public function testFilterPublicLinksKeepsOnlyPublicProjects()
    {
        $this->createFixtures();

        $links = $this->container['taskLinkModel']->getAllGroupedByLabel(1);
        $filtered = $this->callFilterPublicLinks($links);

        $titles = array();

        foreach ($filtered as $labelLinks) {
            foreach ($labelLinks as $link) {
                $titles[] = $link['title'];
            }
        }

        $this->assertContains('Public task', $titles);
        $this->assertNotContains('Private task', $titles);
    }

    private function callFilterPublicLinks(array $links)
    {
        $controller = new TaskViewController($this->container);
        $method = new \ReflectionMethod(TaskViewController::class, 'filterPublicLinks');
        $method->setAccessible(true);

        return $method->invoke($controller, $links);
    }

    private function createFixtures()
    {
        $projectModel = $this->container['projectModel'];
        $taskCreationModel = $this->container['taskCreationModel'];
        $taskLinkModel = $this->container['taskLinkModel'];

        $this->assertEquals(1, $projectModel->create(array('name' => 'Public project')));
        $this->assertTrue($projectModel->enablePublicAccess(1));

        $this->assertEquals(2, $projectModel->create(array('name' => 'Private project')));

        $this->assertEquals(1, $taskCreationModel->create(array('title' => 'Host task', 'project_id' => 1)));
        $this->assertEquals(2, $taskCreationModel->create(array('title' => 'Public task', 'project_id' => 1)));
        $this->assertEquals(3, $taskCreationModel->create(array('title' => 'Private task', 'project_id' => 2)));

        $this->assertNotFalse($taskLinkModel->create(1, 2, 1));
        $this->assertNotFalse($taskLinkModel->create(1, 3, 1));
    }
}
