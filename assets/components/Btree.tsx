import {  useEffect, useState } from 'react'

import axios from 'axios';
import { nanoid } from 'nanoid';
import { CustomizedTreeView, StyledTreeItem } from './TreeView';
import React from 'react';
import TreeItem from '@mui/lab/TreeItem';

const API_URL = 'http://127.0.0.1:8000/api/';

export const Btree = (props: any) => {
  const [loaded, setLoaded] = useState<boolean>(false);
  const [btreeData, setBtreeData] = useState<any>([])

  useEffect(() => {
    fetchBtreeData().then((response)=> {
      setBtreeData(response.data);
      setLoaded(true);
      }).catch((err) => console.log(err))
  }, [])

  const fetchBtreeData = () => {
    return axios.get(API_URL + 'btree')
  }

  if (!loaded) return null;
 


 const RecursiveComponent = ({ data }: any) => {
  return (
    <React.Fragment key={nanoid(11)}>
      {data.map(([key, value]: any) => {
         return (
          <React.Fragment key={nanoid(11)}>
            {value && typeof(value) === 'object' &&
            <StyledTreeItem key={nanoid(11)} nodeId={key} label={key} >
              { <RecursiveComponent key={nanoid(11)} data={Object.entries(value)} />}
              </StyledTreeItem>
            }
            {value && typeof(value) === 'string' &&
            
            <TreeItem key={nanoid(11)} nodeId={key + '_' + nanoid(3)} label={value} icon={null} />
            }
          </React.Fragment>
        );
      })}
      </React.Fragment>
  );
};

  return (<div style={props.style}><CustomizedTreeView key={nanoid(11)} children={RecursiveComponent({data: Object.entries(btreeData)})}></CustomizedTreeView></div>);
}
