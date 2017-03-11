<?php

/* modules/contrib/slick/templates/slick.html.twig */
class __TwigTemplate_78c4457bb00a6b32399e8cfd0acf126b64537e4077cb7dd8a4b01b1575f49e95 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'slick_content' => array($this, 'block_slick_content'),
            'slick_arrow' => array($this, 'block_slick_arrow'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("set" => 31, "if" => 58, "block" => 62, "for" => 63);
        $filters = array("join" => 36, "clean_class" => 37, "raw" => 72, "striptags" => 72);
        $functions = array();

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('set', 'if', 'block', 'for'),
                array('join', 'clean_class', 'raw', 'striptags'),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 31
        $context["classes"] = array(0 => "slick", 1 => (($this->getAttribute(        // line 33
($context["settings"] ?? null), "unslick", array())) ? ("unslick") : ("")), 2 => ((((        // line 34
($context["display"] ?? null) == "main") && $this->getAttribute(($context["settings"] ?? null), "blazy", array()))) ? ("blazy") : ("")), 3 => (($this->getAttribute(        // line 35
($context["settings"] ?? null), "vertical", array())) ? ("slick--vertical") : ("")), 4 => (($this->getAttribute($this->getAttribute(        // line 36
($context["settings"] ?? null), "attributes", array()), "class", array())) ? (twig_join_filter($this->getAttribute($this->getAttribute(($context["settings"] ?? null), "attributes", array()), "class", array()), " ")) : ("")), 5 => (($this->getAttribute(        // line 37
($context["settings"] ?? null), "skin", array())) ? (("slick--skin--" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(($context["settings"] ?? null), "skin", array())))) : ("")), 6 => ((twig_in_filter("boxed", $this->getAttribute(        // line 38
($context["settings"] ?? null), "skin", array()))) ? ("slick--skin--boxed") : ("")), 7 => ((twig_in_filter("split", $this->getAttribute(        // line 39
($context["settings"] ?? null), "skin", array()))) ? ("slick--skin--split") : ("")), 8 => (($this->getAttribute(        // line 40
($context["settings"] ?? null), "optionset", array())) ? (("slick--optionset--" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(($context["settings"] ?? null), "optionset", array())))) : ("")), 9 => ((        // line 41
array_key_exists("arrow_down_attributes", $context)) ? ("slick--has-arrow-down") : ("")), 10 => (($this->getAttribute(        // line 42
($context["settings"] ?? null), "asNavFor", array())) ? (("slick--" . \Drupal\Component\Utility\Html::getClass(($context["display"] ?? null)))) : ("")), 11 => ((($this->getAttribute(        // line 43
($context["settings"] ?? null), "slidesToShow", array()) > 1)) ? ("slick--multiple-view") : ("")), 12 => ((($this->getAttribute(        // line 44
($context["settings"] ?? null), "count", array()) <= $this->getAttribute(($context["settings"] ?? null), "slidesToShow", array()))) ? ("slick--less") : ("")), 13 => ((((        // line 45
($context["display"] ?? null) == "main") && $this->getAttribute(($context["settings"] ?? null), "media_switch", array()))) ? (("slick--" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(($context["settings"] ?? null), "media_switch", array())))) : ("")), 14 => ((((        // line 46
($context["display"] ?? null) == "thumbnail") && $this->getAttribute(($context["settings"] ?? null), "thumbnail_caption", array()))) ? ("slick--has-caption") : ("")));
        // line 50
        $context["arrow_classes"] = array(0 => "slick__arrow", 1 => (($this->getAttribute(        // line 52
($context["settings"] ?? null), "vertical", array())) ? ("slick__arrow--v") : ("")), 2 => (($this->getAttribute(        // line 53
($context["settings"] ?? null), "skin_arrows", array())) ? (("slick__arrow--" . \Drupal\Component\Utility\Html::getClass($this->getAttribute(($context["settings"] ?? null), "skin_arrows", array())))) : ("")));
        // line 56
        echo "
<div";
        // line 57
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["attributes"] ?? null), "addClass", array(0 => ($context["classes"] ?? null)), "method"), "html", null, true));
        echo ">
  ";
        // line 58
        if ( !$this->getAttribute(($context["settings"] ?? null), "unslick", array())) {
            // line 59
            echo "    <div";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["content_attributes"] ?? null), "addClass", array(0 => "slick__slider"), "method"), "html", null, true));
            echo ">
  ";
        }
        // line 61
        echo "
  ";
        // line 62
        $this->displayBlock('slick_content', $context, $blocks);
        // line 67
        echo "
  ";
        // line 68
        if ( !$this->getAttribute(($context["settings"] ?? null), "unslick", array())) {
            // line 69
            echo "    </div>
    ";
            // line 70
            $this->displayBlock('slick_arrow', $context, $blocks);
            // line 82
            echo "  ";
        }
        // line 83
        echo "</div>
";
    }

    // line 62
    public function block_slick_content($context, array $blocks = array())
    {
        // line 63
        echo "    ";
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["items"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
            // line 64
            echo "      ";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $context["item"], "html", null, true));
            echo "
    ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 66
        echo "  ";
    }

    // line 70
    public function block_slick_arrow($context, array $blocks = array())
    {
        // line 71
        echo "      <nav";
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute(($context["arrow_attributes"] ?? null), "addClass", array(0 => ($context["arrow_classes"] ?? null)), "method"), "html", null, true));
        echo ">
        ";
        // line 72
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(strip_tags($this->getAttribute(($context["settings"] ?? null), "prevArrow", array()), "<a><em><span><strong><button><div>")));
        echo "
        ";
        // line 73
        if (array_key_exists("arrow_down_attributes", $context)) {
            // line 74
            echo "          <button";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute($this->getAttribute($this->getAttribute($this->getAttribute(($context["arrow_down_attributes"] ?? null), "addClass", array(0 => "slick-down"), "method"), "setAttribute", array(0 => "type", 1 => "button"), "method"), "setAttribute", array(0 => "data-target", 1 => $this->getAttribute(            // line 76
($context["settings"] ?? null), "downArrowTarget", array())), "method"), "setAttribute", array(0 => "data-offset", 1 => $this->getAttribute(            // line 77
($context["settings"] ?? null), "downArrowOffset", array())), "method"), "html", null, true));
            echo "></button>
        ";
        }
        // line 79
        echo "        ";
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->renderVar(strip_tags($this->getAttribute(($context["settings"] ?? null), "nextArrow", array()), "<a><em><span><strong><button><div>")));
        echo "
      </nav>
    ";
    }

    public function getTemplateName()
    {
        return "modules/contrib/slick/templates/slick.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  146 => 79,  141 => 77,  140 => 76,  138 => 74,  136 => 73,  132 => 72,  127 => 71,  124 => 70,  120 => 66,  111 => 64,  106 => 63,  103 => 62,  98 => 83,  95 => 82,  93 => 70,  90 => 69,  88 => 68,  85 => 67,  83 => 62,  80 => 61,  74 => 59,  72 => 58,  68 => 57,  65 => 56,  63 => 53,  62 => 52,  61 => 50,  59 => 46,  58 => 45,  57 => 44,  56 => 43,  55 => 42,  54 => 41,  53 => 40,  52 => 39,  51 => 38,  50 => 37,  49 => 36,  48 => 35,  47 => 34,  46 => 33,  45 => 31,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "modules/contrib/slick/templates/slick.html.twig", "/var/www/AED/modules/contrib/slick/templates/slick.html.twig");
    }
}
