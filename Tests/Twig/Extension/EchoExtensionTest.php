<?php

namespace Admingenerator\GeneratorBundle\Tests\Twig\Extension;

use Admingenerator\GeneratorBundle\Twig\Extension\EchoExtension;

/**
 * This class test the Admingenerator\GeneratorBundle\Twig\Extension\EchoExtension
 *
 * @author Cedric LOMBARDOT
 * @author Stéphane Escandell
 */
class EchoExtensionTest extends BaseExtensionTest
{
    /**
     * @return \Twig_Extension
     */
    protected function getTestedExtension()
    {
        return new EchoExtension();
    }

    /**
     * @return array
     */
    protected function getTwigVariables()
    {
        $object =  new TestObject();

        return array(
            'obj'  => $object,
            'name' => 'cedric',
            'arr'  => array('obj' => 'val'),
        );
    }

    public function testGetEchoTrans()
    {
        $tpls = array(
            'string' => '{{ echo_trans( "foo" ) }}',
            'variable_key' => '{{ echo_trans( name ) }}',
        );

        $returns = array(
            'string' => array(
                '{% trans from "Admingenerator" %}foo{% endtrans %}',
                'trans return a good trans tag with string elements'
             ),
            'variable_key' => array(
                '{% trans from "Admingenerator" %}cedric{% endtrans %}',
                'trans return a good trans tag with variable as key'
             ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoTransWithParameters()
    {
        $tpls = array(
            'string' => "{{ echo_trans('Display all <b>%foo% %bar%</b> results',{ 'foo': 'foo', 'bar': 'bar' }) }}",
            'variable_key' => '{{ echo_trans( name,{ \'foo\': \'foo\', \'bar\': \'bar\' } ) }}',
        );

        $returns = array(
            'string' => array(
                '{% trans with {\'%foo%\': \'foo\',\'%bar%\': \'bar\',} from "Admingenerator" %}Display all <b>%foo% %bar%</b> results{% endtrans %}',
                'trans return a good trans tag with string elements'
             ),
            'variable_key' => array(
                '{% trans with {\'%foo%\': \'foo\',\'%bar%\': \'bar\',} from "Admingenerator" %}cedric{% endtrans %}',
                'trans return a good trans tag with variable as key'
             ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoTransWithParameterBag()
    {
        $tpls = array(
            'string_bc' => "{{ echo_trans('You\'re editing {{ Book.title }} written by {{ Book.author.name }}!') }}",
            'string_with_full_param_bag' => "{{ echo_trans('You\'re editing %book% written by %author%!|{ %book%: Book.title, %author%: Book.author.name }|') }}",
            'string_with_abbrev_param_bag' => "{{ echo_trans('You\'re editing %Book.title% written by %Book.author.name%!|{ Book.title, Book.author.name }|') }}",
            'string_with_full_param_bag_and_params' => "{{ echo_trans('You\'re editing %book% written by %foo%!|{ %book%: Book.title }|',{ 'foo': 'foo' }) }}",
            'string_with_abbrev_param_bag_and_params' => "{{ echo_trans('You\'re editing %Book.title% written by %foo%!|{ Book.title }|',{ 'foo': 'foo' }) }}",
        );

        $returns = array(
            'string_bc' => array(
                '{% trans with {\'%Book.title%\': Book.title,\'%Book.author.name%\': Book.author.name,} from "Admingenerator" %}You\'re editing %Book.title% written by %Book.author.name%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
            'string_with_full_param_bag' => array(
                '{% trans with {\'%book%\': Book.title,\'%author%\': Book.author.name,} from "Admingenerator" %}You\'re editing %book% written by %author%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
            'string_with_abbrev_param_bag' => array(
                '{% trans with {\'%Book.title%\': Book.title,\'%Book.author.name%\': Book.author.name,} from "Admingenerator" %}You\'re editing %Book.title% written by %Book.author.name%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
            'string_with_full_param_bag_and_params' => array(
                '{% trans with {\'%foo%\': \'foo\',\'%book%\': Book.title,} from "Admingenerator" %}You\'re editing %book% written by %foo%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
            'string_with_abbrev_param_bag_and_params' => array(
                '{% trans with {\'%foo%\': \'foo\',\'%Book.title%\': Book.title,} from "Admingenerator" %}You\'re editing %Book.title% written by %foo%!{% endtrans %}',
                'trans return a good trans tag with string elements'
            ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoPath()
    {
        $tpls = array(
            'string' => '{{ echo_path( "foo" ) }}',
            'variable' => '{{ echo_path( name ) }}',
            'array' => '{{ echo_path( arr.obj ) }}',
            'string_filtered' => '{{ echo_path( "foo", null, ["foo", "bar"] ) }}',
            'variable_filtered' => '{{ echo_path( name, null, ["foo", "bar"] ) }}',
            'array_filtered' => '{{ echo_path( arr.obj, null, ["foo", "bar"] ) }}',
        );

        $returns = array(
            'string' => array(
                '{{ path("foo") }}',
                'Path return a good Path tag with string elements'
             ),
            'variable' => array(
                '{{ path("cedric") }}',
                'Path return a good Path tag with variable'
             ),
            'array' => array(
                '{{ path("val") }}',
                'Path return a good Path tag with array element'
             ),
            'string_filtered' => array(
                '{{ path("foo")|foo|bar }}',
                'Path return a good Path tag with string elements and filters'
             ),
            'variable_filtered' => array(
                '{{ path("cedric")|foo|bar }}',
                'Path return a good Path tag with variable and filters'
             ),
            'array_filtered' => array(
                '{{ path("val")|foo|bar }}',
                'Path return a good Path tag with array element and filters'
             ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoIfGranted()
    {
        $tpls = array(
            'simple'  => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')" ) }}',
            'complex' => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')\') or (hasRole(\'ROLE_B\') and hasRole(\'ROLE_C\')" ) }}',
            'with_object' => '{{ echo_if_granted ( "hasRole(\'ROLE_A\')", \'modelName\' ) }}',
        );

        $returns = array(
            'simple'  => array(
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\') %}',
                'If granted work with a simple role'),
            'complex' => array(
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\') or (hasRole(\'ROLE_B\') and hasRole(\'ROLE_C\')\') %}',
                'If granted work with a complex role expression'
            ),
            'with_object' => array(
                '{% if is_expr_granted(\'hasRole(\'ROLE_A\')\', modelName) %}',
                'If granted work with an object'
            ),
        );

        $this->runTwigTests($tpls, $returns);
    }

    public function testGetEchoRender()
    {
        $tpls = array(
            'controller'  => '{{ echo_render( "MyController" ) }}',
            'with_params' => '{{ echo_render( "MyController", {"hello": name } ) }}',
        );

        $returns = array(
            'controller' => array(
                '{{ render(controller("MyController", {  })) }}',
                'controller return a good controller tag'
            ),
            'with_params' => array(
                '{{ render(controller("MyController", { hello: \'cedric\' })) }}',
                'controller return a good controller tag'
            ),
        );

        $this->runTwigTests($tpls, $returns);
    }
}
