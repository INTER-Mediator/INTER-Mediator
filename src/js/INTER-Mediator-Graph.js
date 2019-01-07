/*
 * INTER-Mediator
 * Copyright (c) INTER-Mediator Directive Committee (http://inter-mediator.org)
 * This project started at the end of 2009 by Masayuki Nii msyk@msyk.net.
 *
 * INTER-Mediator is supplied under MIT License.
 * Please see the full license for details:
 * https://github.com/INTER-Mediator/INTER-Mediator/blob/master/dist-docs/License.txt
 */

// JSHint support
/* global INTERMediator, INTERMediatorOnPage, IMLibMouseEventDispatch, IMLibUI, IMLibKeyDownEventDispatch,
 IMLibChangeEventDispatch, INTERMediator_DBAdapter, IMLibQueue, IMLibCalc, IMLibPageNavigation,
 IMLibEventResponder, Parser, IMLibLocalContext, IMLibFormat, IMLibInputEventDispatch */
/* jshint -W083 */ // Function within a loop

/**
 * @fileoverview IMLib and INTERMediatorLib classes are defined here.
 */
/*

 IMLibNodeGraph object can handle the directed acyclic graph.
 The nodes property stores every node, i.e. the id attribute of each node.
 The edges property stores ever edge represented by the objet {from: node1, to: node2}.
 If the node1 or node2 aren't stored in the nodes array, they are going to add as nodes too.

 The following is the example to store the directed acyclic graph.

 a -> b -> c -> d
 |    -> f
 ------>
 -> e
 i -> j
 x

 IMLibNodeGraph.clear()
 IMLibNodeGraph.addEdge('a','b')
 IMLibNodeGraph.addEdge('b','c')
 IMLibNodeGraph.addEdge('c','d')
 IMLibNodeGraph.addEdge('a','e')
 IMLibNodeGraph.addEdge('b','f')
 IMLibNodeGraph.addEdge('a','f')
 IMLibNodeGraph.addEdge('i','j')
 IMLibNodeGraph.addNode('x')

 The first calling of the getLeafNodesWithRemoving method returns 'd', 'f', 'e', 'j', 'x'.
 The second calling does 'c', 'i'. The third one does 'b', the forth one does 'a'.
 You can get the nodes from leaves to root as above.

 If the getLeafNodesWithRemoving method returns [] (no elements array), and the nodes property has any elements,
 it shows the graph has circular reference.

 */
var IMLibNodeGraph = {
  nodes: [],
  edges: [],
  clear: function () {
    'use strict'
    this.nodes = []
    this.edges = []
  },
  addNode: function (node) {
    'use strict'
    if (this.nodes.indexOf(node) < 0) {
      this.nodes.push(node)
    }
  },
  addEdge: function (fromNode, toNode) {
    'use strict'
    if (this.nodes.indexOf(fromNode) < 0) {
      this.addNode(fromNode)
    }
    if (this.nodes.indexOf(toNode) < 0) {
      this.addNode(toNode)
    }
    this.edges.push({from: fromNode, to: toNode})
  },
  getAllNodesInEdge: function () {
    'use strict'
    var i
    let nodes = []
    for (i = 0; i < this.edges.length; i += 1) {
      if (nodes.indexOf(this.edges[i].from) < 0) {
        nodes.push(this.edges[i].from)
      }
      if (nodes.indexOf(this.edges[i].to) < 0) {
        nodes.push(this.edges[i].to)
      }
    }
    return nodes
  },
  getLeafNodes: function () {
    'use strict'
    var i
    let srcs = []
    let dests = []
    let srcAndDests = this.getAllNodesInEdge()
    for (i = 0; i < this.edges.length; i += 1) {
      srcs.push(this.edges[i].from)
    }
    for (i = 0; i < this.edges.length; i += 1) {
      if (srcs.indexOf(this.edges[i].to) < 0 && dests.indexOf(this.edges[i].to) < 0) {
        dests.push(this.edges[i].to)
      }
    }
    for (i = 0; i < this.nodes.length; i += 1) {
      if (srcAndDests.indexOf(this.nodes[i]) < 0) {
        dests.push(this.nodes[i])
      }
    }
    return dests
  },
  getLeafNodesWithRemoving: function () {
    'use strict'
    var i
    let newEdges = []
    let dests = this.getLeafNodes()
    for (i = 0; i < this.edges.length; i += 1) {
      if (dests.indexOf(this.edges[i].to) < 0) {
        newEdges.push(this.edges[i])
      }
    }
    this.edges = newEdges
    for (i = 0; i < dests.length; i += 1) {
      this.nodes.splice(this.nodes.indexOf(dests[i]), 1)
    }
    return dests
  },
  removeNode: function (node) {
    'use strict'
    var i
    let newEdges = []
    for (i = 0; i < this.edges.length; i += 1) {
      if (this.edges[i].to !== node) {
        newEdges.push(this.edges[i])
      }
    }
    this.edges = newEdges
    this.nodes.splice(this.nodes.indexOf(node), 1)
  },
  applyToAllNodes: function (f) {
    'use strict'
    var i
    for (i = 0; i < this.nodes.length; i += 1) {
      f(this.nodes[i])
    }
  }
}

// @@IM@@IgnoringRestOfFile
module.exports = IMLibNodeGraph
